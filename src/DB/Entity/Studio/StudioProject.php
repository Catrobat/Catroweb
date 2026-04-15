<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'studio_program')]
#[ORM\Entity(repositoryClass: StudioProjectRepository::class)]
class StudioProject
{
  #[ORM\Id]
  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'studio', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Studio::class, cascade: ['persist'])]
  protected Studio $studio;

  #[ORM\JoinColumn(name: 'activity', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\OneToOne(targetEntity: StudioActivity::class, cascade: ['persist'])]
  protected StudioActivity $activity;

  #[ORM\JoinColumn(name: 'program', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist'])]
  protected Project $project;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
  protected User $user;

  #[ORM\Column(name: 'updated_on', type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $updated_on = null;

  #[ORM\Column(name: 'created_on', type: Types::DATETIME_MUTABLE, nullable: false)]
  protected \DateTime $created_on;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): StudioProject
  {
    $this->id = $id;

    return $this;
  }

  public function getStudio(): Studio
  {
    return $this->studio;
  }

  public function setStudio(Studio $studio): StudioProject
  {
    $this->studio = $studio;

    return $this;
  }

  public function getActivity(): StudioActivity
  {
    return $this->activity;
  }

  public function setActivity(StudioActivity $activity): StudioProject
  {
    $this->activity = $activity;

    return $this;
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function setProject(Project $project): StudioProject
  {
    $this->project = $project;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): StudioProject
  {
    $this->user = $user;

    return $this;
  }

  public function getUpdatedOn(): ?\DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?\DateTime $updated_on): StudioProject
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function getCreatedOn(): \DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(\DateTime $created_on): StudioProject
  {
    $this->created_on = $created_on;

    return $this;
  }
}
