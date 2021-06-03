<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class StudioProgram.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Studios\StudioProgramRepository")
 * @ORM\Table(name="studio_program")
 */
class StudioProgram
{
  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id;

  /**
   * @ORM\ManyToOne(targetEntity="Studio", cascade={"persist"})
   * @ORM\JoinColumn(name="studio", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected Studio $studio;

  /**
   * @ORM\OneToOne(targetEntity="StudioActivity", cascade={"persist"})
   * @ORM\JoinColumn(name="activity", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected StudioActivity $activity;

  /**
   * @ORM\ManyToOne(targetEntity="Program", cascade={"persist"})
   * @ORM\JoinColumn(name="program", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected Program $program;

  /**
   * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected User $user;

  /**
   * @ORM\Column(name="updated_on", type="datetime", nullable=true)
   */
  protected ?DateTime $updated_on;

  /**
   * @ORM\Column(name="created_on", type="datetime", nullable=false)
   */
  protected DateTime $created_on;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function getStudio(): Studio
  {
    return $this->studio;
  }

  public function setStudio(Studio $studio): void
  {
    $this->studio = $studio;
  }

  public function getActivity(): StudioActivity
  {
    return $this->activity;
  }

  public function setActivity(StudioActivity $activity): void
  {
    $this->activity = $activity;
  }

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function setProgram(Program $program): void
  {
    $this->program = $program;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): void
  {
    $this->user = $user;
  }

  public function getUpdatedOn(): ?DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?DateTime $updated_on): void
  {
    $this->updated_on = $updated_on;
  }

  public function getCreatedOn(): DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(DateTime $created_on): void
  {
    $this->created_on = $created_on;
  }
}
