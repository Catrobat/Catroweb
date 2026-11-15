<?php

declare(strict_types=1);

namespace App\System\BackgroundJob;

use App\DB\Entity\System\BackgroundJob;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

/**
 * Handlers record the failures they expect themselves. Anything else (a crash, or retries used up) still has to
 * end the job, or its client polls forever.
 */
#[AsEventListener]
final readonly class BackgroundJobFailureListener
{
  public function __construct(private Connection $connection)
  {
  }

  public function __invoke(WorkerMessageFailedEvent $event): void
  {
    $message = $event->getEnvelope()->getMessage();
    if ($event->willRetry() || !$message instanceof TrackedJobMessage) {
      return;
    }

    // Plain SQL: after a database error the EntityManager is closed, and the job must still end.
    $this->connection->executeStatement(
      'UPDATE background_job SET state = :failed, error_status = 500, error_message = :message, updated_at = :now WHERE id = :id AND state IN (:queued, :running)',
      ['failed' => BackgroundJob::FAILED, 'message' => 'Something went wrong. Please try again later.', 'now' => new \DateTimeImmutable()->format('Y-m-d H:i:s'),
        'id' => $message->jobId(), 'queued' => BackgroundJob::QUEUED, 'running' => BackgroundJob::RUNNING],
    );
  }
}
