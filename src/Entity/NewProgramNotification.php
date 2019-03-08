<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NewProgramNotification extends CatroNotification
{

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true)
   */
  private $program;

  /**
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "Notifications/NotificationTypes/program_follow_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user
   * @param      $title
   * @param      $message
   * @param      $program
   *
   */
  public function __construct(User $user, $title, $message, $program)
  {
    parent::__construct($user, $title, $message);
    $this->program = $program;
  }

  /**
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @param $program
   */
  public function setProgram($program)
  {
    $this->program = $program;
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