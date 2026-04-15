<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StudioProjectNotification extends CatroNotification
{
  private string $twig_template = 'User/Notification/Type/StudioProject.html.twig';

  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'studio_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Studio::class)]
    private ?Studio $studio,
    #[ORM\JoinColumn(name: 'project_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $project_user,
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Project::class)]
    private ?Project $project,
  ) {
    parent::__construct($user, '', '', 'studio');
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function setStudio(?Studio $studio): void
  {
    $this->studio = $studio;
  }

  public function getProjectUser(): ?User
  {
    return $this->project_user;
  }

  public function setProjectUser(?User $project_user): void
  {
    $this->project_user = $project_user;
  }

  public function getProject(): ?Project
  {
    return $this->project;
  }

  public function setProject(?Project $project): void
  {
    $this->project = $project;
  }

  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
