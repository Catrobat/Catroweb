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
   * must not block the next one for a day. Only uploads being completed right now are never evicted.
   *
   * @throws ResumableUploadException when the file is too large or all of the user's open uploads are being completed
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
      if (null !== $lock) {
        $this->delete($old);
        $this->unlock($lock);
        --$excess;
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
      $this->delete($upload);

      return null;
    }

    return $upload;
  }

  /**
   * @throws ResumableUploadException when the index is out of range, the size is wrong or the checksum doesn't match
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

    // Written under a temporary name and renamed, so a half-written chunk is never counted as received.
    $target = $this->dir($upload->id).'/'.$index.'.part';
    $chunk->move($this->dir($upload->id), $index.'.part.tmp');
    $this->filesystem->rename($target.'.tmp', $target, true);
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
      throw new ResumableUploadException(ResumableUploadException::BAD_CHECKSUM);
    }

    return new File($path);
  }

  /**
   * Exclusive, non-blocking: completing an upload outlasts Cloudflare's timeout, so clients retry while the first
   * request is still working. The retry must not join or delete the same files.
   *
   * @return resource|null null when another request holds the lock or the upload is gone
   */
  public function lock(ResumableUpload $upload): mixed
  {
    $handle = @fopen($this->dir($upload->id).'/.lock', 'c');
    if (false === $handle) {
      return null;
    }

    if (!flock($handle, LOCK_EX | LOCK_NB)) {
      fclose($handle);

      return null;
    }

    return $handle;
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
   * Frees the disk but keeps the upload until it expires, so a client whose completion request timed out can still
   * learn which project it created.
   */
  public function markCompleted(ResumableUpload $upload, string $project_id): void
  {
    $dir = $this->dir($upload->id);
    $this->filesystem->remove([...(glob($dir.'/*.part') ?: []), $dir.'/project.catrobat']);
    $this->filesystem->dumpFile(
      $dir.'/'.self::META,
      json_encode(['completed_project_id' => $project_id] + $upload->toArray(), JSON_THROW_ON_ERROR),
    );
  }

  public function delete(ResumableUpload $upload): void
  {
    $this->filesystem->remove($this->dir($upload->id));
  }

  public function purgeExpired(): void
  {
    foreach ($this->allIds() as $id) {
      $upload = $this->read($id);
      if (!$upload instanceof ResumableUpload || $upload->isExpired()) {
        $this->filesystem->remove($this->dir($id));
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
        && null === $upload->completed_project_id,
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
