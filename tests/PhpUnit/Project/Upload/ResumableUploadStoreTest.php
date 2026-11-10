<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Upload;

use App\DB\Entity\User\User;
use App\Project\Upload\ResumableUpload;
use App\Project\Upload\ResumableUploadException;
use App\Project\Upload\ResumableUploadStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 */
#[CoversClass(ResumableUploadStore::class)]
#[CoversClass(ResumableUpload::class)]
final class ResumableUploadStoreTest extends TestCase
{
  private string $root;

  private string $scratch;

  #[\Override]
  protected function setUp(): void
  {
    $this->root = sys_get_temp_dir().'/resumable-store-test-'.bin2hex(random_bytes(4));
    $this->scratch = $this->root.'-scratch';
    mkdir($this->scratch, 0o777, true);
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove([$this->root, $this->scratch]);
  }

  public function testChunksSentOutOfOrderAndResentAssembleToTheOriginalFile(): void
  {
    $store = $this->store();
    $content = random_bytes(2 * ResumableUploadStore::CHUNK_SIZE + 1234);
    $upload = $store->start($this->user('u1'), strlen($content), md5($content), 'pocketcode', false, null);

    $this->assertSame(3, $upload->total_chunks);
    foreach ([2, 0, 1, 1] as $index) {
      $this->sendChunk($store, $upload, $content, $index);
    }

    $this->assertSame([0, 1, 2], $store->receivedChunks($upload));
    $this->assertSame($content, file_get_contents($store->assemble($upload)->getPathname()));
  }

  public function testAFileOverTheLimitIsRefused(): void
  {
    $this->expectExceptionMessage(ResumableUploadException::TOO_LARGE);
    $this->store(max: 1000)->start($this->user('u1'), 1001, null, 'pocketcode', false, null);
  }

  public function testStartingOneUploadTooManyDiscardsTheOldestThatIsNotBeingCompleted(): void
  {
    $store = $this->store();
    $first = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $second = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $theirs = $store->start($this->user('someone-else'), 10, null, 'pocketcode', false, null);

    $third = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->assertNull($store->find($first->id, $this->user('u1')));
    $this->assertNotNull($store->find($second->id, $this->user('u1')));
    $this->assertNotNull($store->find($theirs->id, $this->user('someone-else')));

    // Both remaining uploads are being completed right now: neither may be thrown away.
    $locks = [$store->lock($second), $store->lock($third)]; // held until the end of the test
    $this->assertNotContains(null, $locks);
    $this->expectExceptionMessage(ResumableUploadException::TOO_MANY_OPEN);
    $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
  }

  public function testOnlyOneRequestCompletesAnUploadAndACompletedOneRemembersItsProject(): void
  {
    $store = $this->store();
    $content = random_bytes(10);
    $upload = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->sendChunk($store, $upload, $content, 0);

    $lock = $store->lock($upload);
    $this->assertNotNull($lock);
    $this->assertNull($store->lock($upload), 'a second lock must fail while the first is held');
    $store->assemble($upload);
    $store->markCompleted($upload, 'project-1');
    $store->unlock($lock);

    $completed = $store->find($upload->id, $this->user('u1'));
    $this->assertNotNull($completed);
    $this->assertSame('project-1', $completed->completed_project_id);
    $this->assertSame([], $store->receivedChunks($completed));
    $this->assertFileDoesNotExist($this->root.'/'.$upload->id.'/project.catrobat');
    $this->assertNotNull($store->lock($completed));

    try {
      $this->sendChunk($store, $upload, $content, 0);
      $this->fail('A completed upload must not take chunks again');
    } catch (ResumableUploadException $e) {
      $this->assertSame(ResumableUploadException::FINISHED, $e->getMessage());
    }

    $this->assertSame([], $store->receivedChunks($completed));
  }

  public function testARejectedUploadKeepsItsReasonAndNoLongerCountsAsOpen(): void
  {
    $store = $this->store();
    $content = random_bytes(10);
    $rejected = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->sendChunk($store, $rejected, $content, 0);
    $store->markFailed($rejected, 'validation_error', 'Invalid project file');

    $found = $store->find($rejected->id, $this->user('u1'));
    $this->assertNotNull($found);
    $this->assertSame(['validation_error', 'Invalid project file'], [$found->failure_type, $found->failure_message]);
    $this->assertSame([], $store->receivedChunks($found));

    $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->assertNotNull($store->find($rejected->id, $this->user('u1')), 'finished uploads are not evicted');
  }

  public function testAChunkIsRefusedWhileTheUploadIsBeingCompleted(): void
  {
    $store = $this->store();
    $upload = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $lock = $store->lock($upload);
    $this->assertNotNull($lock);

    $this->expectExceptionMessage(ResumableUploadException::BUSY);
    $this->sendChunk($store, $upload, random_bytes(10), 0);
  }

  public function testAChunkWithTheWrongSizeIndexOrChecksumIsRefused(): void
  {
    $store = $this->store();
    $content = random_bytes(ResumableUploadStore::CHUNK_SIZE + 10);
    $upload = $store->start($this->user('u1'), strlen($content), null, 'pocketcode', false, null);

    $cases = [
      ResumableUploadException::BAD_INDEX => [2, substr($content, 0, 10), null],
      ResumableUploadException::BAD_CHUNK_SIZE => [1, substr($content, 0, 9), null],
      ResumableUploadException::BAD_CHECKSUM => [1, substr($content, -10), md5('something else')],
    ];
    foreach ($cases as $reason => [$index, $bytes, $checksum]) {
      try {
        $store->storeChunk($upload, $index, $this->file($bytes), $checksum ?? md5($bytes));
        $this->fail('Expected '.$reason);
      } catch (ResumableUploadException $e) {
        $this->assertSame($reason, $e->getMessage());
      }
    }

    $this->assertSame([], $store->receivedChunks($upload));
  }

  public function testCompletingNeedsEveryChunkAndAMatchingWholeFileChecksum(): void
  {
    $store = $this->store();
    $content = random_bytes(ResumableUploadStore::CHUNK_SIZE + 10);
    $upload = $store->start($this->user('u1'), strlen($content), md5('not this file'), 'pocketcode', false, null);
    $this->sendChunk($store, $upload, $content, 0);

    try {
      $store->assemble($upload);
      $this->fail('Expected missing chunks');
    } catch (ResumableUploadException $e) {
      $this->assertSame(ResumableUploadException::INCOMPLETE, $e->getMessage());
    }

    $this->sendChunk($store, $upload, $content, 1);

    try {
      $store->assemble($upload);
      $this->fail('Expected a checksum mismatch');
    } catch (ResumableUploadException $e) {
      $this->assertSame(ResumableUploadException::BAD_CHECKSUM, $e->getMessage());
    }

    $this->assertFileDoesNotExist($this->root.'/'.$upload->id.'/project.catrobat');
    $this->assertSame([0, 1], $store->receivedChunks($upload));
  }

  public function testAfterAServerSideFailureTheChunksStayForTheNextAttempt(): void
  {
    $store = $this->store();
    $content = random_bytes(10);
    $upload = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->sendChunk($store, $upload, $content, 0);

    $store->assemble($upload);
    $store->discardAssembled($upload);

    $this->assertFileDoesNotExist($this->root.'/'.$upload->id.'/project.catrobat');
    $this->assertSame($content, file_get_contents($store->assemble($upload)->getPathname()));
  }

  public function testExpiredUploadsInUseAndFreshDirectoriesWithoutMetadataSurviveThePurge(): void
  {
    $store = $this->store();
    $in_use = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $abandoned = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->expire($in_use->id);
    $this->expire($abandoned->id);
    mkdir($this->root.'/'.str_repeat('a', 32));
    mkdir($this->root.'/'.str_repeat('b', 32));
    touch($this->root.'/'.str_repeat('b', 32), time() - 7200);

    $lock = $store->lock($in_use);
    $this->assertNotNull($lock);
    $store->purgeExpired();

    $this->assertDirectoryExists($this->root.'/'.$in_use->id);
    $this->assertDirectoryDoesNotExist($this->root.'/'.$abandoned->id);
    $this->assertDirectoryExists($this->root.'/'.str_repeat('a', 32), 'a start() may be writing it right now');
    $this->assertDirectoryDoesNotExist($this->root.'/'.str_repeat('b', 32));
  }

  public function testFinishingAnUploadRestartsItsExpiry(): void
  {
    $store = $this->store();
    $upload = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);
    $this->expire($upload->id, '+1 minute');

    $store->markCompleted($upload, 'project-1');

    $found = $store->find($upload->id, $this->user('u1'));
    $this->assertNotNull($found);
    $this->assertGreaterThan(new \DateTimeImmutable('+23 hours'), $found->expires_at);
  }

  public function testOnlyTheOwnerFindsAnUploadAndExpiredOnesAreGone(): void
  {
    $store = $this->store();
    $upload = $store->start($this->user('u1'), 10, null, 'pocketcode', false, null);

    $this->assertInstanceOf(ResumableUpload::class, $store->find($upload->id, $this->user('u1')));
    $this->assertNull($store->find($upload->id, $this->user('intruder')));
    $this->assertNull($store->find('../../etc', $this->user('u1')));

    $this->expire($upload->id);

    $this->assertNull($store->find($upload->id, $this->user('u1')));
    $this->assertDirectoryDoesNotExist($this->root.'/'.$upload->id);
  }

  private function expire(string $id, string $at = '-1 minute'): void
  {
    $meta = $this->root.'/'.$id.'/upload.json';
    $data = json_decode((string) file_get_contents($meta), true);
    $data['expires_at'] = new \DateTimeImmutable($at)->format(\DATE_ATOM);
    file_put_contents($meta, json_encode($data, JSON_THROW_ON_ERROR));
  }

  private function store(int $max = 100 * 1024 * 1024): ResumableUploadStore
  {
    return new ResumableUploadStore($this->root, $max);
  }

  private function user(string $id): User
  {
    $user = new User();
    $user->setId($id);

    return $user;
  }

  private function sendChunk(ResumableUploadStore $store, ResumableUpload $upload, string $content, int $index): void
  {
    $bytes = substr($content, $index * $upload->chunk_size, $upload->expectedChunkSize($index));
    $store->storeChunk($upload, $index, $this->file($bytes), md5($bytes));
  }

  private function file(string $bytes): File
  {
    $path = (string) tempnam($this->scratch, 'chunk');
    file_put_contents($path, $bytes);

    return new File($path);
  }
}
