<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommentNotification extends CatroNotification
{
  /**
   * @var UserComment The UserComment which triggered this CommentNotification. If the UserComment gets deleted,
   *                  this CommentNotification gets deleted as well.
   *
   * @ORM\OneToOne(
   *   targetEntity="\App\Entity\UserComment",
   *   inversedBy="notification"
   * )
   * @ORM\JoinColumn(
   *   name="comment_id",
   *   referencedColumnName="id",
   *   onDelete="SET NULL",
   *   nullable=true
   * )
   */
  private $comment;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = "Notifications/NotificationTypes/comment_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user The user to which this CommentNotification should be shown.
   * @param UserComment $comment The UserComment which triggered this CommentNotification.
   *
   */
  public function __construct(User $user, $comment)
  {
    parent::__construct($user);
    $this->comment = $comment;
  }

  /**
   * Returns the UserComment which triggered this CommentNotification.
   *
   * @return UserComment The UserComment which triggered this CommentNotification.
   */
  public function getComment()
  {
    return $this->comment;
  }

  /**
   * Sets the UserComment which triggered this CommentNotification.
   *
   * @param UserComment $comment The UserComment which triggered this CommentNotification.
   */
  public function setComment($comment)
  {
    $this->comment = $comment;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }
}