<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="click_statistics")
 */
class ClickStatistic
{
  use BaseRequestStatistics;

  /**
   * @ORM\Column(type="text", options={"default": ""}, nullable=false)
   */
  protected ?string $type = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Tag", inversedBy="tags")
   * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=true)
   */
  protected ?Tag $tag = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Extension", inversedBy="extension")
   * @ORM\JoinColumn(name="extension_id", referencedColumnName="id", nullable=true)
   */
  protected ?Extension $extension = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected ?Program $program = null;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected ?int $scratch_program_id = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="rec_from_program_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected ?Program $recommended_from_program = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false}, nullable=true)
   */
  protected bool $user_specific_recommendation = false;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $clicked_at = null;

  public function getProgram(): ?Program
  {
    return $this->program;
  }

  public function setProgram(?Program $program): void
  {
    $this->program = $program;
  }

  public function getScratchProgramId(): ?int
  {
    return $this->scratch_program_id;
  }

  public function setScratchProgramId(?int $scratch_program_id): void
  {
    $this->scratch_program_id = $scratch_program_id;
  }

  public function getRecommendedFromProgram(): ?Program
  {
    return $this->recommended_from_program;
  }

  public function setRecommendedFromProgram(?Program $recommended_from_program): void
  {
    $this->recommended_from_program = $recommended_from_program;
  }

  public function getUserSpecificRecommendation(): bool
  {
    return $this->user_specific_recommendation;
  }

  public function setUserSpecificRecommendation(bool $is_user_specific_recommendation): void
  {
    $this->user_specific_recommendation = $is_user_specific_recommendation;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): void
  {
    $this->type = $type;
  }

  public function getTag(): ?Tag
  {
    return $this->tag;
  }

  public function setTag(?Tag $tag): void
  {
    $this->tag = $tag;
  }

  public function getExtension(): ?Extension
  {
    return $this->extension;
  }

  public function setExtension(?Extension $extension): void
  {
    $this->extension = $extension;
  }

  public function getClickedAt(): ?DateTime
  {
    return $this->clicked_at;
  }

  public function setClickedAt(?DateTime $clicked_at): void
  {
    $this->clicked_at = $clicked_at;
  }
}
