<?php

namespace App\Api_deprecated\Responses;

/**
 * @deprecated
 */
class ProgramListResponse
{
  public function __construct(
    private readonly array $programs,
    private readonly int $total_programs
  ) {
  }

  public function getPrograms(): array
  {
    return $this->programs;
  }

  public function getTotalPrograms(): int
  {
    return $this->total_programs;
  }
}
