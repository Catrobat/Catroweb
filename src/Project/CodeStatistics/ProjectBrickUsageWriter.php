<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\DB\Entity\Project\ProjectCodeStatistics;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Keeps project_brick_usage equal to the brick counts of a project's newest code statistics. Every new statistics
 * row is the newest one, so persisting it replaces the project's usage rows (upload, lazy parse, backfill, fixtures).
 *
 * The rows are written after the statistics flush has committed, in statements of their own: the usage is a derived
 * copy, and a failure writing it must never roll back the statistics or break the request.
 */
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ProjectCodeStatistics::class)]
#[AsDoctrineListener(event: Events::postFlush)]
class ProjectBrickUsageWriter implements ResetInterface
{
  /** Real code.xml types are class names; anything else is uploader input not worth storing. */
  private const string BRICK_TYPE_PATTERN = '/^[A-Za-z][A-Za-z0-9_]{0,99}$/';

  /** @var array<string, array<string, int>> */
  private array $pending = [];

  public function __construct(
    private readonly Connection $connection,
    private readonly LoggerInterface $logger,
  ) {
  }

  public static function isValidBrickType(string $brick_type): bool
  {
    return 1 === preg_match(self::BRICK_TYPE_PATTERN, $brick_type);
  }

  public function postPersist(ProjectCodeStatistics $stats): void
  {
    $project_id = $stats->getProject()->getId();
    if (null !== $project_id) {
      $this->pending[$project_id] = $stats->getBrickCounts();
    }
  }

  public function postFlush(): void
  {
    $pending = $this->pending;
    $this->pending = [];
    foreach ($pending as $project_id => $brick_counts) {
      $this->replace($project_id, $brick_counts);
    }
  }

  /**
   * @param array<string, int> $brick_counts
   */
  public function replace(string $project_id, array $brick_counts): void
  {
    $values = [];
    $params = [];
    foreach ($brick_counts as $brick_type => $count) {
      $brick_type = (string) $brick_type;
      if (self::isValidBrickType($brick_type) && $count > 0) {
        $values[] = '(?, ?, ?)';
        array_push($params, $project_id, $brick_type, (int) $count);
      }
    }

    try {
      $this->connection->delete('project_brick_usage', ['project_id' => $project_id]);
      if ([] !== $values) {
        // Upsert: a concurrent replace of the same project (two first views, backfill and upload) must not fail
        $this->connection->executeStatement(
          'INSERT INTO project_brick_usage (project_id, brick_type, count) VALUES '.implode(', ', $values)
          .' ON DUPLICATE KEY UPDATE count = VALUES(count)',
          $params,
        );
      }
    } catch (\Throwable $e) {
      $this->logger->error('Writing the brick usage of project '.$project_id.' failed: '.$e->getMessage(), ['exception' => $e]);
    }
  }

  #[\Override]
  public function reset(): void
  {
    $this->pending = [];
  }
}
