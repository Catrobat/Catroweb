<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LikeNotification extends CatroNotification
{
  /**
   * @var User The User which "like action" to another user triggered this LikeNotification. If this user gets deleted,
   *           this LikeNotification gets deleted as well.
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
  private $like_from;

  /**
   * @var Program the Program about which this LikeNotification is notifying, belongs to
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
  private $program;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = '/Notifications/NotificationTypes/like_notification.html.twig';

  /**
   * LikeNotification constructor.
   *
   * @param User    $user      the User to which this LikeNotification will be shown
   * @param User    $like_from the User which "like action" to another user triggered this LikeNotification
   * @param Program $program   the Program to which the ProgramLike and this LikeNotification is notifying, belongs to
   */
  public function __construct(User $user, $like_from, $program)
  {
    parent::__construct($user);
    $this->like_from = $like_from;
    $this->program = $program;
  }

  /**
   * Returns the User which "like action" to another user triggered this LikeNotification.
   *
   * @return User the User which "like action" to another user triggered this LikeNotification
   */
  public function getLikeFrom()
  {
    return $this->like_from;
  }

  /**
   * Sets the User which "like action" to another user triggered this LikeNotification.
   *
   * @param User $like_from the User which "like action" to another user triggered this LikeNotification
   */
  public function setLikeFrom($like_from)
  {
    $this->like_from = $like_from;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   *
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }

  /**
   * Returns the Program to which the ProgramLike and this LikeNotification is notifying, belongs to.
   *
   * @return Program the Program to which the ProgramLike and this LikeNotification is notifying, belongs to
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * Sets the Program to which the ProgramLike and this LikeNotification is notifying, belongs to.
   *
   * @param Program $program the Program to which the ProgramLike and this LikeNotification is notifying, belongs to
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }
}
