<?php

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RemixNotification extends CatroNotification
{
  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = '/Notifications/NotificationTypes/remix_notification.html.twig';

  /**
   * RemixNotification constructor.
   *
   * @param User    $user          the User to which this RemixNotification will be shown
   * @param User    $remix_from    the owner of the parent Project
   * @param Project $project       the parent Project
   * @param Project $remix_project the newly remixed child Project
   */
  public function __construct(User $user, /**
   * the owner of the parent Project.
   *
   * @ORM\ManyToOne(
   *     targetEntity=User::class
   * )
   *
   * @ORM\JoinColumn(
   *     name="remix_root",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
    private ?User $remix_from, /**
   *  the parent Project.
   *
   * @ORM\ManyToOne(
   *     targetEntity=Project::class,
   *     inversedBy="remix_notification_mentions_as_parent"
   * )
   *
   * @ORM\JoinColumn(
   *     name="project_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
    private ?Project $project, /**
   * the newly remixed child Project.
   *
   * @ORM\ManyToOne(
   *     targetEntity=Project::class,
   *     inversedBy="remix_notification_mentions_as_child"
   * )
   *
   * @ORM\JoinColumn(
   *     name="remix_project_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
    private ?Project $remix_project)
  {
    parent::__construct($user, '', '', 'remix');
  }

  /**
   * Returns the owner of the parent Project.
   */
  public function getRemixFrom(): ?User
  {
    return $this->remix_from;
  }

  /**
   * Sets the owner of the parent Project.
   */
  public function setRemixFrom(?User $remix_from): void
  {
    $this->remix_from = $remix_from;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }

  /**
   * Returns the parent Project.
   */
  public function getProject(): ?Project
  {
    return $this->project;
  }

  /**
   * Sets the parent Project.
   */
  public function setProject(?Project $project): void
  {
    $this->project = $project;
  }

  /**
   * Returns the child Project.
   */
  public function getRemixProject(): ?Project
  {
    return $this->remix_project;
  }

  /**
   * Sets the child Project.
   */
  public function setRemixProject(?Project $remix_project): void
  {
    $this->remix_project = $remix_project;
  }
}
