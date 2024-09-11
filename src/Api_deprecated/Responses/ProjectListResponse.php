<?php

declare(strict_types=1);

namespace App\Api_deprecated\Responses;

/**
 * @deprecated
 */
readonly class ProjectListResponse
{
  public function __construct(
    private array $projects,
    private int $total_projects,
  ) {
  }

  public function getProjects(): array
  {
    return $this->projects;
  }

  public function getTotalProjects(): int
  {
    return $this->total_projects;
  }
}
