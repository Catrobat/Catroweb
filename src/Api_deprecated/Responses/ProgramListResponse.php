<?php

namespace App\Api_deprecated\Responses;

/**
 * @deprecated
 */
class ProgramListResponse
{
  private array $programs;
  private int $total_programs;

  public function __construct($programs, $total_programs)
  {
    $this->programs = $programs;
    $this->total_programs = $total_programs;
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
