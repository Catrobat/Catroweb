<?php

namespace Catrobat\AppBundle\Responses;

class ProgramListResponse
{

  private $programs;

  private $total_programs;

  private $show_details;

  private $is_user_specific_recommendation;

  public function __construct($programs, $total_programs, $show_details = true, $is_user_specific_recommendation = false)
  {
    $this->programs = $programs;
    $this->total_programs = $total_programs;
    $this->show_details = $show_details;
    $this->is_user_specific_recommendation = $is_user_specific_recommendation;
  }

  public function getPrograms()
  {
    return $this->programs;
  }

  public function getTotalPrograms()
  {
    return $this->total_programs;
  }

  public function getShowDetails()
  {
    return $this->show_details;
  }

  public function isIsUserSpecificRecommendation()
  {
    return $this->is_user_specific_recommendation;
  }
}