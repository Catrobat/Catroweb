<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramInappropriateReportRepository;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramInappropriateReport.
 */
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProgramInappropriateReportRepository::class)]
class ProgramInappropriateReport
{
  final public const int STATUS_NEW = 1;

  final public const int STATUS_REJECTED = 2;

  final public const int STATUS_ACCEPTED = 3;

  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  private ?int $id = null;

  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reports_triggered_by_this_user')]
  private ?User $reporting_user = null;

  #[ORM\JoinColumn(name: 'user_id_rep', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reports_of_this_user')]
  private ?User $reported_user = null;

  #[ORM\Column(name: 'category', type: Types::TEXT, length: 256)]
  private ?string $category = null;

  #[ORM\Column(name: 'note', type: Types::TEXT)]
  private ?string $note = null;

  #[ORM\Column(name: 'time', type: Types::DATETIME_MUTABLE)]
  private ?\DateTime $time = null;

  #[ORM\Column(type: Types::INTEGER)]
  private ?int $state = null;

  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'reports')]
  private ?Program $program = null;

  #[ORM\Column(name: 'projectVersion', type: Types::INTEGER)]
  private int $projectVersion;

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    if (!$this->getTime() instanceof \DateTime) {
      $this->setTime(TimeUtils::getDateTime());
    }
  }

  #[ORM\PrePersist]
  public function updateState(): void
  {
    if (null === $this->getState()) {
      $this->setState(self::STATUS_NEW);
    }
  }

  #[ORM\PrePersist]
  public function updateProgramVersion(): void
  {
    $this->setProjectVersion($this->getProgram()->getVersion());
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setReportingUser(?User $reporting_user): ProgramInappropriateReport
  {
    $this->reporting_user = $reporting_user;

    return $this;
  }

  public function getReportingUser(): ?User
  {
    return $this->reporting_user;
  }

  public function setReportedUser(?User $reported_user): ProgramInappropriateReport
  {
    $this->reported_user = $reported_user;

    return $this;
  }

  public function getReportedUser(): ?User
  {
    return $this->reported_user;
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

  public function setTime(\DateTime $time): ProgramInappropriateReport
  {
    $this->time = $time;

    return $this;
  }

  public function getTime(): ?\DateTime
  {
    return $this->time;
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function setState(int $state): ProgramInappropriateReport
  {
    if (!in_array($state, [self::STATUS_NEW, self::STATUS_ACCEPTED, self::STATUS_REJECTED], true)) {
      throw new \InvalidArgumentException('Invalid state');
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
