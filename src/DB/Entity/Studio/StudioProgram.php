<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioProgramRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'studio_program')]
#[ORM\Entity(repositoryClass: StudioProgramRepository::class)]
class StudioProgram
{
  #[ORM\Id]
  #[ORM\Column(name: 'id', type: 'integer')]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'studio', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Studio::class, cascade: ['persist'])]
  protected Studio $studio;

  #[ORM\JoinColumn(name: 'activity', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\OneToOne(targetEntity: StudioActivity::class, cascade: ['persist'])]
  protected StudioActivity $activity;

  #[ORM\JoinColumn(name: 'program', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Program::class, cascade: ['persist'])]
  protected Program $program;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
  protected User $user;

  #[ORM\Column(name: 'updated_on', type: 'datetime', nullable: true)]
  protected ?\DateTime $updated_on = null;

  #[ORM\Column(name: 'created_on', type: 'datetime', nullable: false)]
  protected \DateTime $created_on;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): StudioProgram
  {
    $this->id = $id;

    return $this;
  }

  public function getStudio(): Studio
  {
    return $this->studio;
  }

  public function setStudio(Studio $studio): StudioProgram
  {
    $this->studio = $studio;

    return $this;
  }

  public function getActivity(): StudioActivity
  {
    return $this->activity;
  }

  public function setActivity(StudioActivity $activity): StudioProgram
  {
    $this->activity = $activity;

    return $this;
  }

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function setProgram(Program $program): StudioProgram
  {
    $this->program = $program;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): StudioProgram
  {
    $this->user = $user;

    return $this;
  }

  public function getUpdatedOn(): ?\DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?\DateTime $updated_on): StudioProgram
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function getCreatedOn(): \DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(\DateTime $created_on): StudioProgram
  {
    $this->created_on = $created_on;

    return $this;
  }
}
