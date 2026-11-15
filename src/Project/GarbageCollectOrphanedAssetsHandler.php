<?php

declare(strict_types=1);

namespace App\Project;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Deletes one batch, then queues the next one behind it while orphans remain. The chain runs on the "cleanup" queue,
 * whose own worker does nothing else, so a backlog of any size never holds up other jobs.
 */
#[AsMessageHandler]
final readonly class GarbageCollectOrphanedAssetsHandler
{
  public const int BATCH = 1000;

  /** An upload maps its assets within seconds; a day leaves room for any slow or retried one. */
  public const string GRACE_PERIOD = '-1 day';

  public function __construct(
    private ProjectDeduplicationService $deduplication_service,
    private MessageBusInterface $bus,
    private LoggerInterface $cleanupLogger,
    private int $batch_size = self::BATCH,
  ) {
  }

  public function __invoke(GarbageCollectOrphanedAssets $message): void
  {
    $batch = $this->deduplication_service->garbageCollectBatch(new \DateTimeImmutable(self::GRACE_PERIOD), $this->batch_size, $message->after);
    $this->cleanupLogger->info('cleanup_run', ['rule' => 'orphaned_assets', 'deleted' => $batch['count'], 'bytes' => $batch['bytes']]);

    if ($batch['more']) {
      $this->bus->dispatch(new GarbageCollectOrphanedAssets($batch['last']));
    }
  }
}
