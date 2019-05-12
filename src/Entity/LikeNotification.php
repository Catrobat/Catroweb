<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LikeNotification extends CatroNotification
{
  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User")
   * @ORM\JoinColumn(name="like_from", referencedColumnName="id", nullable=true)
   */
  private $like_from;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   * @var Program
   */
  private $program;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = "/Notifications/NotificationTypes/like_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user
   * @param      $like_from
   * @param      $program
   */
  public function __construct(User $user, $like_from, $program)
  {
    parent::__construct($user);
    $this->like_from = $like_from;
    $this->program = $program;
  }

  /**
   * @return mixed
   */
  public function getLikeFrom()
  {
    return $this->like_from;
  }

  /**
   * @param $like_from
   */
  public function setLikeFrom($like_from)
  {
    $this->like_from = $like_from;
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

  /**
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @param Program $program
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }
}