<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LikeNotification extends CatroNotification
{
  /**
   * he User which "like action" to another user triggered this LikeNotification.
   * If this user gets deleted, this LikeNotification gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\User",
   *     inversedBy="like_notification_mentions"
   * )
   * @ORM\JoinColumn(
   *     name="like_from",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private ?User $like_from = null;

  /**
   * the Program about which this LikeNotification is notifying, belongs to.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\Program",
   *     inversedBy="like_notification_mentions"
   * )
   * @ORM\JoinColumn(
   *     name="program_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private ?Program $program = null;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = '/Notifications/NotificationTypes/like_notification.html.twig';

  /**
   * LikeNotification constructor.
   *
   * @param User    $user      the User to which this LikeNotification will be shown
   * @param User    $like_from the User which "like action" to another user triggered this LikeNotification
   * @param Program $program   the Program to which the ProgramLike and this LikeNotification is notifying, belongs to
   */
  public function __construct(User $user, User $like_from, Program $program)
  {
    parent::__construct($user, '', '', 'reaction');
    $this->like_from = $like_from;
    $this->program = $program;
  }

  /**
   * Returns the User which "like action" to another user triggered this LikeNotification.
   */
  public function getLikeFrom(): ?User
  {
    return $this->like_from;
  }

  /**
   * Sets the User which "like action" to another user triggered this LikeNotification.
   */
  public function setLikeFrom(?User $like_from): void
  {
    $this->like_from = $like_from;
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
   * Returns the Program to which the ProgramLike and this LikeNotification is notifying, belongs to.
   */
  public function getProgram(): ?Program
  {
    return $this->program;
  }

  /**
   * Sets the Program to which the ProgramLike and this LikeNotification is notifying, belongs to.
   */
  public function setProgram(?Program $program): void
  {
    $this->program = $program;
  }
}
