<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/**
 * ProgramInappropriateReport.
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ProgramInappropriateReportRepository")
 */
class ProgramInappropriateReport
{
  const STATUS_NEW = 1;
  const STATUS_REJECTED = 2;
  const STATUS_ACCEPTED = 3;

  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private ?int $id = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="program_inappropriate_reports")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  private ?User $reportingUser = null;

  /**
   * @ORM\Column(name="category", type="text", length=256)
   */
  private ?string $category = null;

  /**
   * @ORM\Column(name="note", type="text")
   */
  private ?string $note = null;

  /**
   * @ORM\Column(name="time", type="datetime")
   */
  private ?DateTime $time = null;

  /**
   * @ORM\Column(type="integer")
   */
  private ?int $state = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="reports")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", onDelete="SET NULL")
   */
  private ?Program $program = null;

  /**
   * @ORM\Column(name="projectVersion", type="integer")
   */
  private int $projectVersion;

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function updateTimestamps(): void
  {
    if (null === $this->getTime())
    {
      $this->setTime(TimeUtils::getDateTime());
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function updateState(): void
  {
    if (null === $this->getState())
    {
      $this->setState(self::STATUS_NEW);
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function updateProgramVersion(): void
  {
    $this->setProjectVersion($this->getProgram()->getVersion());
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setReportingUser(?User $reportingUser): ProgramInappropriateReport
  {
    $this->reportingUser = $reportingUser;

    return $this;
  }

  public function getReportingUser(): ?User
  {
    return $this->reportingUser;
  }

  public function setCategory(string $category): ProgramInappropriateReport
  {
    $this->category = $category;

    return $this;
  }

  public function getCategory(): ?string
  {
    return $this->category;
  }

  public function setNote(string $note): ProgramInappropriateReport
  {
    $this->note = $note;

    return $this;
  }

  public function getNote(): ?string
  {
    return $this->note;
  }

  public function setTime(DateTime $time): ProgramInappropriateReport
  {
    $this->time = $time;

    return $this;
  }

  public function getTime(): ?DateTime
  {
    return $this->time;
  }

  /**
   * @throws InvalidArgumentException
   */
  public function setState(int $state): ProgramInappropriateReport
  {
    if (!in_array($state, [self::STATUS_NEW, self::STATUS_ACCEPTED, self::STATUS_REJECTED], true))
    {
      throw new InvalidArgumentException('Invalid state');
    }
    $this->state = $state;

    return $this;
  }

  public function getState(): ?int
  {
    return $this->state;
  }

  public function setProgram(?Program $program): ProgramInappropriateReport
  {
    $this->program = $program;

    return $this;
  }

  public function getProgram(): ?Program
  {
    return $this->program;
  }

  public function setProjectVersion(int $projectVersion): ProgramInappropriateReport
  {
    $this->projectVersion = $projectVersion;

    return $this;
  }

  public function getProjectVersion(): int
  {
    return $this->projectVersion;
  }
}
