<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * How often a project's code uses a brick type: a queryable copy of the latest ProjectCodeStatistics::brick_counts
 * (a JSON column cannot be searched per brick). Written by ProjectBrickUsageWriter.
 */
#[ORM\Table(name: 'project_brick_usage')]
#[ORM\Index(name: 'pbu_brick_type_idx', columns: ['brick_type'])]
#[ORM\Entity]
class ProjectBrickUsage
{
  public function __construct(
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Project $project,
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $brick_type,
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $count = 0,
  ) {
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function getBrickType(): string
  {
    return $this->brick_type;
  }

  public function getCount(): int
  {
    return $this->count;
  }
}
