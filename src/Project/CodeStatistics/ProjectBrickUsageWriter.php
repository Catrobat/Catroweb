<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\DB\Entity\Project\ProjectCodeStatistics;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Events;

/**
 * Keeps project_brick_usage equal to the brick counts of a project's newest code statistics. Every new statistics
 * row is the newest one, so persisting it replaces the project's usage rows (upload, lazy parse, backfill, fixtures).
 */
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ProjectCodeStatistics::class)]
class ProjectBrickUsageWriter
{
  public function __construct(
    private readonly Connection $connection,
  ) {
  }

  public function postPersist(ProjectCodeStatistics $stats): void
  {
    $project_id = $stats->getProject()->getId();
    if (null !== $project_id) {
      $this->replace($project_id, $stats->getBrickCounts());
    }
  }

  /**
   * @param array<string, int> $brick_counts
   */
  public function replace(string $project_id, array $brick_counts): void
  {
    $this->connection->delete('project_brick_usage', ['project_id' => $project_id]);
    foreach ($brick_counts as $brick_type => $count) {
      $brick_type = (string) $brick_type;
      if ('' === $brick_type || strlen($brick_type) > 255 || $count <= 0) {
        continue;
      }

      $this->connection->insert('project_brick_usage', [
        'project_id' => $project_id,
        'brick_type' => $brick_type,
        'count' => $count,
      ]);
    }
  }
}
