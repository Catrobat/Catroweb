<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="program_downloads")
 */
class ProgramDownloads
{
  use BaseRequestStatistics;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program_downloads")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=false)
   */
  protected Program $program;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected ?int $recommended_by_page_id = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="recommended_by_program_id", referencedColumnName="id", nullable=true)
   */
  protected ?Program $recommended_by_program = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false}, nullable=true)
   */
  protected bool $user_specific_recommendation = false;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="rec_from_program_id", referencedColumnName="id", nullable=true)
   */
  protected ?Program $recommended_from_program_via_tag = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $downloaded_at = null;

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function setProgram(Program $program): void
  {
    $this->program = $program;
  }

  public function getRecommendedFromProgramViaTag(): ?Program
  {
    return $this->recommended_from_program_via_tag;
  }

  public function setRecommendedFromProgramViaTag(?Program $recommended_from_program_via_tag): void
  {
    $this->recommended_from_program_via_tag = $recommended_from_program_via_tag;
  }

  public function getDownloadedAt(): ?DateTime
  {
    return $this->downloaded_at;
  }

  public function setDownloadedAt(?DateTime $downloaded_at): void
  {
    $this->downloaded_at = $downloaded_at;
  }

  public function getRecommendedByPageId(): ?int
  {
    return $this->recommended_by_page_id;
  }

  public function setRecommendedByPageId(int $recommended_by_page_id): void
  {
    $this->recommended_by_page_id = $recommended_by_page_id;
  }

  public function getRecommendedByProgram(): ?Program
  {
    return $this->recommended_by_program;
  }

  public function setRecommendedByProgram(Program $recommended_by_program): void
  {
    $this->recommended_by_program = $recommended_by_program;
  }

  public function getUserSpecificRecommendation(): bool
  {
    return $this->user_specific_recommendation;
  }

  public function setUserSpecificRecommendation(bool $is_user_specific_recommendation): void
  {
    $this->user_specific_recommendation = $is_user_specific_recommendation;
  }
}
