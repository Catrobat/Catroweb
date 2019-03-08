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
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "Notifications/NotificationTypes/comment_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user
   * @param      $title
   * @param      $message
   * @param      $comment
   *
   */
  public function __construct(User $user, $title, $message, $comment)
  {
    parent::__construct($user, $title, $message);
    $this->comment = $comment;
    /* if you didn't forget to set the member variable to default above
       you don't need the following line */
    $this->twig_template = "Notifications/NotificationTypes/comment_notification.html.twig";
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