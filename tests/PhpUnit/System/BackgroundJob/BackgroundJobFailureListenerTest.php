<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\BackgroundJob;

use App\DB\Entity\System\BackgroundJob;
use App\Project\Scratch\ConvertScratchProject;
use App\System\BackgroundJob\BackgroundJobFailureListener;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

/**
 * @internal
 */
#[CoversClass(BackgroundJobFailureListener::class)]
final class BackgroundJobFailureListenerTest extends TestCase
{
  public function testAJobWhoseRetriesAreUsedUpFails(): void
  {
    $job = $this->failJob(will_retry: false);

    $this->assertSame(BackgroundJob::FAILED, $job->getState());
    $this->assertSame(500, $job->getErrorStatus());
  }

  public function testAJobThatWillBeRetriedKeepsWaiting(): void
  {
    $this->assertSame(BackgroundJob::QUEUED, $this->failJob(will_retry: true)->getState());
  }

  private function failJob(bool $will_retry): BackgroundJob
  {
    $job = new BackgroundJob('scratch_import', '1');
    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('isOpen')->willReturn(true);
    $entity_manager->method('find')->willReturn($job);
    $event = new WorkerMessageFailedEvent(new Envelope(new ConvertScratchProject($job->getId(), 1, null)), 'scratch', new \RuntimeException('crash'));
    if ($will_retry) {
      $event->setForRetry();
    }

    new BackgroundJobFailureListener($entity_manager)($event);

    return $job;
  }
}
