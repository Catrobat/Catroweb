<?php

declare(strict_types=1);

namespace App\Project\Upload;

use App\DB\Entity\User\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Resumable project uploads: a project file sent in chunks that each pass Cloudflare's and nginx's request size
 * limits. Every upload is a directory holding its metadata and one file per received chunk; nothing is in the
 * database, and expired uploads are swept whenever a new one starts.
 */
class ResumableUploadStore
{
  public const int CHUNK_SIZE = 16 * 1024 * 1024;

  public const int MAX_OPEN_UPLOADS_PER_USER = 2;

  private const string TTL = '+24 hours';

  private const string META = 'upload.json';

  /** Created with the upload and never again, so a lock attempt can't recreate a directory being removed. */
  private const string LOCK = '.lock';

  private readonly Filesystem $filesystem;

  public function __construct(
    #[Autowire('%kernel.project_dir%/var/resumable_uploads')]
    private readonly string $root,
    #[Autowire('%env(int:PROJECT_UPLOAD_MAX_BYTES)%')]
    private readonly int $max_file_size,
  ) {
    $this->filesystem = new Filesystem();
  }

  public function getMaxFileSize(): int
  {
    return $this->max_file_size;
  }

  /**
   * At the per-user limit the oldest open uploads make room: an upload abandoned by a closed tab or a crashed app
   * must not block the next one for a day. Uploads in use right now (a chunk arriving, being completed) are spared.
   *
   * @throws ResumableUploadException when the file is too large or all of the user's open uploads are in use
   */
  public function start(User $user, int $file_size, ?string $checksum, string $flavor, bool $private, ?string $project_id): ResumableUpload
  {
    if ($file_size < 1 || $file_size > $this->max_file_size) {
      throw new ResumableUploadException(ResumableUploadException::TOO_LARGE);
    }

    $this->purgeExpired();
    $open = $this->openUploadsOf($user);
    $excess = count($open) - self::MAX_OPEN_UPLOADS_PER_USER + 1;
    foreach ($open as $old) {
      if ($excess <= 0) {
        break;
      }

      $lock = $this->lock($old);
      if (null === $lock) {
        continue;
      }

      try {
        // The list was read before locking: an upload completed in between keeps its answer for the client, and
        // one aborted in between has already made room.
        if (!$this->read($old->id)?->isFinished()) {
          $this->delete($old);
          --$excess;
        }
      } finally {
        $this->unlock($lock);
      }
    }

    if ($excess > 0) {
      throw new ResumableUploadException(ResumableUploadException::TOO_MANY_OPEN);
    }

    $upload = new ResumableUpload(
      bin2hex(random_bytes(16)),
      (string) $user->getId(),
      $file_size,
      self::CHUNK_SIZE,
      (int) ceil($file_size / self::CHUNK_SIZE),
      null === $checksum || '' === $checksum ? null : strtolower($checksum),
      $flavor,
      $private,
      $project_id,
      new \DateTimeImmutable(self::TTL),
    );
    $this->filesystem->dumpFile($this->dir($upload->id).'/'.self::META, json_encode($upload->toArray(), JSON_THROW_ON_ERROR));
    $this->filesystem->touch($this->dir($upload->id).'/'.self::LOCK);

    return $upload;
  }

  /**
   * The upload, if it exists, has not expired and belongs to the user; otherwise null, so callers answer 404
   * without telling strangers which uploads exist.
   */
  public function find(string $id, User $user): ?ResumableUpload
  {
    if (1 !== preg_match('/^[a-f0-9]{32}$/', $id)) {
      return null;
    }

    $upload = $this->read($id);
    if (!$upload instanceof ResumableUpload || $upload->user_id !== (string) $user->getId()) {
      return null;
    }

    if ($upload->isExpired()) {
      $this->removeUnlessLocked($id);

      return null;
    }

    return $upload;
  }

  /**
   * Chunks of one upload may arrive in parallel (shared lock), but never while the upload is being completed,
   * aborted or evicted, and never after it finished: that would refill the disk outside the per-user limit.
   *
   * @throws ResumableUploadException when the index is out of range, the size is wrong, the checksum doesn't match
   *                                  or the upload no longer takes chunks
   */
  public function storeChunk(ResumableUpload $upload, int $index, File $chunk, string $checksum): void
  {
    if ($index < 0 || $index >= $upload->total_chunks) {
      throw new ResumableUploadException(ResumableUploadException::BAD_INDEX);
    }

    if ($chunk->getSize() !== $upload->expectedChunkSize($index)) {
      throw new ResumableUploadException(ResumableUploadException::BAD_CHUNK_SIZE);
    }

    if (strtolower($checksum) !== md5_file($chunk->getPathname())) {
      throw new ResumableUploadException(ResumableUploadException::BAD_CHECKSUM);
    }

    $lock = $this->lock($upload, true);
    if (null === $lock) {
      throw new ResumableUploadException(ResumableUploadException::BUSY);
    }

    try {
      // Read again under the lock: the upload may have been finished or deleted since the caller found it.
      $current = $this->read($upload->id);
      if (!$current instanceof ResumableUpload) {
        throw new ResumableUploadException(ResumableUploadException::BUSY);
      }

      if ($current->isFinished()) {
        throw new ResumableUploadException(ResumableUploadException::FINISHED);
      }

      // Written under a temporary name and renamed, so a half-written chunk is never counted as received.
      // The temporary name is unique, as two requests may carry the same chunk at once.
      $temporary = $index.'.part.'.bin2hex(random_bytes(4)).'.tmp';
      $chunk->move($this->dir($upload->id), $temporary);
      $this->filesystem->rename($this->dir($upload->id).'/'.$temporary, $this->dir($upload->id).'/'.$index.'.part', true);
    } finally {
      $this->unlock($lock);
    }
  }

  /**
   * @return list<int>
   */
  public function receivedChunks(ResumableUpload $upload): array
  {
    $received = [];
    for ($index = 0; $index < $upload->total_chunks; ++$index) {
      if (is_file($this->dir($upload->id).'/'.$index.'.part')) {
        $received[] = $index;
      }
    }

    return $received;
  }

  /**
   * Joins all chunks into one file inside the upload's directory.
   *
   * @throws ResumableUploadException when chunks are missing or the whole file's checksum doesn't match
   */
  public function assemble(ResumableUpload $upload): File
  {
    if (count($this->receivedChunks($upload)) !== $upload->total_chunks) {
      throw new ResumableUploadException(ResumableUploadException::INCOMPLETE);
    }

    $path = $this->dir($upload->id).'/project.catrobat';
    $out = fopen($path, 'wb');
    if (false === $out) {
      throw new \RuntimeException('Cannot write '.$path);
    }

    try {
      for ($index = 0; $index < $upload->total_chunks; ++$index) {
        $in = fopen($this->dir($upload->id).'/'.$index.'.part', 'rb');
        if (false === $in) {
          throw new \RuntimeException('Cannot read chunk '.$index);
        }

        stream_copy_to_stream($in, $out);
        fclose($in);
      }
    } finally {
      fclose($out);
    }

    if (null !== $upload->checksum && $upload->checksum !== md5_file($path)) {
      $this->filesystem->remove($path);

      throw new ResumableUploadException(ResumableUploadException::BAD_CHECKSUM);
    }

    return new File($path);
  }

  /**
   * Non-blocking. Exclusive for completing, aborting and evicting: completing outlasts Cloudflare's timeout, so
   * clients retry while the first request is still working, and the retry must not join or delete the same files.
   * Shared for storing chunks.
   *
   * @return resource|null null when another request holds a conflicting lock or the upload is gone
   */
  public function lock(ResumableUpload $upload, bool $shared = false): mixed
  {
    return $this->lockDir($upload->id, $shared);
  }

  /**
   * @param resource $lock
   */
  public function unlock(mixed $lock): void
  {
    flock($lock, LOCK_UN);
    fclose($lock);
  }

  /**
   * After a server-side failure: the chunks stay, so completing again needs no re-upload.
   */
  public function discardAssembled(ResumableUpload $upload): void
  {
    $this->filesystem->remove($this->dir($upload->id).'/project.catrobat');
  }

  public function markCompleted(ResumableUpload $upload, string $project_id): void
  {
    $this->finish($upload, ['completed_project_id' => $project_id]);
  }

  public function markFailed(ResumableUpload $upload, string $type, string $message): void
  {
    $this->finish($upload, ['failure_type' => $type, 'failure_message' => $message]);
  }

  public function delete(ResumableUpload $upload): void
  {
    $this->filesystem->remove($this->dir($upload->id));
  }

  public function purgeExpired(): void
  {
    foreach ($this->allIds() as $id) {
      $upload = $this->read($id);
      // Without metadata only when old: a parallel start() creates the directory before it writes the metadata.
      $stale = $upload instanceof ResumableUpload
        ? $upload->isExpired()
        : (int) @filemtime($this->dir($id)) < time() - 3600;
      if ($stale) {
        $this->removeUnlessLocked($id);
      }
    }
  }

  /**
   * Frees the disk but keeps the outcome for a day, so a client whose completion request timed out still gets the
   * created project or the reason for the rejection when it retries.
   *
   * @param array<string, string> $outcome
   */
  private function finish(ResumableUpload $upload, array $outcome): void
  {
    $dir = $this->dir($upload->id);
    $this->filesystem->remove([...(glob($dir.'/*.part') ?: []), $dir.'/project.catrobat']);
    $finished = ResumableUpload::fromArray(['expires_at' => new \DateTimeImmutable(self::TTL)->format(\DATE_ATOM)] + $outcome + $upload->toArray());
    $this->filesystem->dumpFile($dir.'/'.self::META, json_encode($finished->toArray(), JSON_THROW_ON_ERROR));
  }

  /**
   * @return resource|null
   */
  private function lockDir(string $id, bool $shared = false): mixed
  {
    $handle = @fopen($this->dir($id).'/'.self::LOCK, 'r');
    if (false === $handle) {
      return null;
    }

    if (!flock($handle, ($shared ? LOCK_SH : LOCK_EX) | LOCK_NB)) {
      fclose($handle);

      return null;
    }

    return $handle;
  }

  private function removeUnlessLocked(string $id): void
  {
    if (!is_file($this->dir($id).'/'.self::LOCK)) {
      // Left behind by a start() that died before creating the lock file; nobody can be using it.
      $this->filesystem->remove($this->dir($id));

      return;
    }

    $lock = $this->lockDir($id);
    if (null !== $lock) {
      try {
        $this->filesystem->remove($this->dir($id));
      } finally {
        $this->unlock($lock);
      }
    }
  }

  /**
   * @return list<ResumableUpload> oldest first
   */
  private function openUploadsOf(User $user): array
  {
    $open = array_values(array_filter(
      array_map($this->read(...), $this->allIds()),
      static fn (?ResumableUpload $upload): bool => $upload instanceof ResumableUpload
        && $upload->user_id === (string) $user->getId()
        && !$upload->isFinished(),
    ));
    usort($open, static fn (ResumableUpload $a, ResumableUpload $b): int => $a->expires_at <=> $b->expires_at);

    return $open;
  }

  /**
   * @return list<string>
   */
  private function allIds(): array
  {
    if (!is_dir($this->root)) {
      return [];
    }

    return array_values(array_filter(
      scandir($this->root) ?: [],
      static fn (string $entry): bool => 1 === preg_match('/^[a-f0-9]{32}$/', $entry),
    ));
  }

  private function read(string $id): ?ResumableUpload
  {
    $meta = $this->dir($id).'/'.self::META;
    if (!is_file($meta)) {
      return null;
    }

    try {
      return ResumableUpload::fromArray(json_decode((string) file_get_contents($meta), true, 512, JSON_THROW_ON_ERROR));
    } catch (\JsonException|\TypeError|\ValueError) {
      return null;
    }
  }

  private function dir(string $id): string
  {
    return $this->root.'/'.$id;
  }
}
