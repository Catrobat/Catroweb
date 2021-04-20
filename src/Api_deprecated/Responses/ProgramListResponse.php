<?php

namespace App\Api_deprecated\Responses;

/**
 * @deprecated
 */
class ProgramListResponse
{
  private array $programs;
  private int $total_programs;
  private bool $is_user_specific_recommendation;

  public function __construct($programs, $total_programs, bool $is_user_specific_recommendation = false)
  {
    $this->programs = $programs;
    $this->total_programs = $total_programs;
    $this->is_user_specific_recommendation = $is_user_specific_recommendation;
  }

  public function getPrograms(): array
  {
    return $this->programs;
  }

  public function getTotalPrograms(): int
  {
    return $this->total_programs;
  }

  public function isIsUserSpecificRecommendation(): bool
  {
    return $this->is_user_specific_recommendation;
  }
}
