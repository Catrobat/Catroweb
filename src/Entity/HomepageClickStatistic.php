<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="homepage_click_statistics")
 */
class HomepageClickStatistic
{
  use BaseRequestStatistics;

  /**
   * @ORM\Column(type="text", options={"default": ""}, nullable=false)
   */
  protected string $type = '';

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected ?Program $program = null;

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

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): void
  {
    $this->type = $type;
  }

  public function getClickedAt(): ?DateTime
  {
    return $this->clicked_at;
  }

  public function setClickedAt(DateTime $clicked_at): void
  {
    $this->clicked_at = $clicked_at;
  }
}
