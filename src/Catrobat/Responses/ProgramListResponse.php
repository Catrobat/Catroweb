<?php

namespace App\Catrobat\Responses;

/**
 * Class ProgramListResponse
 * @package App\Catrobat\Responses
 */
class ProgramListResponse
{

  /**
   * @var
   */
  private $programs;

  /**
   * @var
   */
  private $total_programs;

  /**
   * @var bool
   */
  private $show_details;

  /**
   * @var bool
   */
  private $is_user_specific_recommendation;

  /**
   * ProgramListResponse constructor.
   *
   * @param      $programs
   * @param      $total_programs
   * @param bool $show_details
   * @param bool $is_user_specific_recommendation
   */
  public function __construct($programs, $total_programs, $show_details = true, $is_user_specific_recommendation = false)
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

  /**
   * @return bool
   */
  public function getShowDetails()
  {
    return $this->show_details;
  }

  /**
   * @return bool
   */
  public function isIsUserSpecificRecommendation()
  {
    return $this->is_user_specific_recommendation;
  }
}