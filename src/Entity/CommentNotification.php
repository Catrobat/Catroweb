<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommentNotification extends CatroNotification
{
  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\UserComment")
   * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=true)
   */
  private $comment;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = "Notifications/NotificationTypes/comment_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user
   * @param      $comment
   *
   */
  public function __construct(User $user, $comment)
  {
    parent::__construct($user);
    $this->comment = $comment;
  }

  /**
   * @return mixed
   */
  public function getComment()
  {
    return $this->comment;
  }

  /**
   * @param $comment
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