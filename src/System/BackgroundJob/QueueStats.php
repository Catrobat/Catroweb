<?php

declare(strict_types=1);

namespace App\System\BackgroundJob;

use App\System\Cron\RunDueCronTasks;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/** What the queues hold right now, for the Background tasks admin page. */
class QueueStats
{
  public const array QUEUES = ['mail', 'default', 'scratch', 'uploads'];

  public function __construct(
    private readonly Connection $connection,
    #[Autowire(service: 'messenger.transport.failed')]
    private readonly TransportInterface $failed_transport,
  ) {
  }

  /**
   * @return array<string, array{waiting: int, running: int, oldest_waiting_seconds: int|null}>
   */
  public function pending(): array
  {
    $stats = array_fill_keys([...self::QUEUES, 'failed'], ['waiting' => 0, 'running' => 0, 'oldest_waiting_seconds' => null]);
    try {
      $rows = $this->connection->fetchAllAssociative(
        'SELECT queue_name, delivered_at IS NULL AS waiting, COUNT(*) AS n, MIN(available_at) AS oldest
         FROM messenger_messages WHERE available_at <= NOW() OR delivered_at IS NOT NULL GROUP BY queue_name, delivered_at IS NULL'
      );
    } catch (\Throwable) {
      return $stats; // no queue table: tests run every job synchronously
    }

    foreach ($rows as $row) {
      $queue = (string) $row['queue_name'];
      $stats[$queue] ??= ['waiting' => 0, 'running' => 0, 'oldest_waiting_seconds' => null];
      if ((bool) $row['waiting']) {
        $stats[$queue]['waiting'] = (int) $row['n'];
        $stats[$queue]['oldest_waiting_seconds'] = max(0, time() - (int) strtotime((string) $row['oldest']));
      } else {
        $stats[$queue]['running'] = (int) $row['n'];
      }
    }

    return $stats;
  }

  /**
   * @return list<array{id: string, message: string, retryable: bool, error: string, failed_at: \DateTimeInterface|null}>
   */
  public function failed(int $limit = 50): array
  {
    if (!$this->failed_transport instanceof ListableReceiverInterface) {
      return [];
    }

    $failed = [];
    foreach ($this->failed_transport->all($limit) as $envelope) {
      $error = $envelope->last(ErrorDetailsStamp::class);
      $failed[] = [
        'id' => (string) $envelope->last(TransportMessageIdStamp::class)?->getId(),
        'message' => $envelope->getMessage()::class,
        'retryable' => self::retryable($envelope->getMessage()),
        'error' => (string) $error?->getExceptionMessage(),
        'failed_at' => $envelope->last(RedeliveryStamp::class)?->getRedeliveredAt(),
      ];
    }

    return $failed;
  }

  /** A scheduler tick has no queue: retried from the admin page it would run a whole lane in that request. */
  public static function retryable(object $message): bool
  {
    return !$message instanceof RunDueCronTasks;
  }
}
