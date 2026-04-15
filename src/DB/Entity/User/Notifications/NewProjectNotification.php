<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class NewProjectNotification extends CatroNotification
{
  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'User/Notification/Type/NewProject.html.twig';

  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'new_program_notification_mentions')]
    private ?Project $project,
  ) {
    parent::__construct($user, '', '', 'follow');
  }

  /**
   * Returns the new Program which triggered this NewProjectNotification.
   */
  public function getProject(): ?Project
  {
    return $this->project;
  }

  /**
   * Sets the new Program which triggered this NewProjectNotification.
   */
  public function setProject(?Project $project): void
  {
    $this->project = $project;
  }

  /**
   * It's important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
