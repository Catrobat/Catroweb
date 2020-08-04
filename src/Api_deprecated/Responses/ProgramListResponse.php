<?php

namespace App\Api_deprecated\Responses;

/**
 * @deprecated
 */
class ProgramListResponse
{
  private $programs;

  private $total_programs;

  private bool $show_details;

  private bool $is_user_specific_recommendation;

  /**
   * ProgramListResponse constructor.
   *
   * @param mixed $programs
   * @param mixed $total_programs
   */
  public function __construct($programs, $total_programs, bool $show_details = true, bool $is_user_specific_recommendation = false)
  {
    $this->programs = $programs;
    $this->total_programs = $total_programs;
    $this->show_details = $show_details;
    $this->is_user_specific_recommendation = $is_user_specific_recommendation;
  }

  /**
   * @return mixed
   */
  public function getPrograms()
  {
    return $this->programs;
  }

  /**
   * @return mixed
   */
  public function getTotalPrograms()
  {
    return $this->total_programs;
  }

  public function getShowDetails(): bool
  {
    return $this->show_details;
  }

  public function isIsUserSpecificRecommendation(): bool
  {
    return $this->is_user_specific_recommendation;
  }
}
