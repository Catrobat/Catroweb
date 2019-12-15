<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RemixNotification extends CatroNotification
{
  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User")
   * @ORM\JoinColumn(name="remix_from", referencedColumnName="id", nullable=true)
   */
  private $remix_from;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   * @var Program
   */
  private $program;


  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="remix_program_id", referencedColumnName="id")
   * @var Program
   */
  private $remix_program;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = "/Notifications/NotificationTypes/remix_notification.html.twig";

  /**
   * RemixNotification constructor.
   *
   * @param User $user
   * @param      $remix_from
   * @param      $program
   * @param      $remix_program
   */
  public function __construct(User $user, $remix_from, $program, $remix_program)
  {
    parent::__construct($user);
    $this->remix_from = $remix_from;
    $this->program = $program;
    $this->remix_program = $remix_program;
  }

  /**
   * @return mixed
   */
  public function getRemixFrom()
  {
    return $this->remix_from;
  }

  /**
   * @param $remix_from
   */
  public function setRemixFrom($remix_from)
  {
    $this->remix_from = $remix_from;
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
  /**
   * @return Program
   */
  public function getRemixProgram()
  {
    return $this->remix_program;
  }

  /**
   * @param Program $remix_program
   */
  public function setRemixProgram($remix_program)
  {
    $this->remix_program = $remix_program;
  }
}
