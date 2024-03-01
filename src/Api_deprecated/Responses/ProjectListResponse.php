<?php

namespace App\Api_deprecated\Responses;

/**
 * @deprecated
 */
class ProjectListResponse
{
  public function __construct(
    private readonly array $projects,
    private readonly int $total_projects
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
