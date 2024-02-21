<?php

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NewProjectNotification extends CatroNotification
{
  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'Notifications/NotificationTypes/new_project_notification.html.twig';

  public function __construct(User $user, /**
   * The new Project which triggered this NewProjectNotification. If this Project gets deleted,
   * this NewProjectNotification gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity=Project::class,
   *     inversedBy="new_project_notification_mentions"
   * )
   *
   * @ORM\JoinColumn(
   *     name="project_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
    private ?Project $project)
  {
    parent::__construct($user, '', '', 'follow');
  }

  /**
   * Returns the new Project which triggered this NewProjectNotification.
   */
  public function getProject(): ?Project
  {
    return $this->project;
  }

  /**
   * Sets the new Project which triggered this NewProjectNotification.
   */
  public function setProject(?Project $project): void
  {
    $this->project = $project;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
