<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RemixNotification extends CatroNotification
{
  /**
   * @var User the owner of the parent Program
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\User"
   * )
   * @ORM\JoinColumn(
   *     name="remix_root",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private $remix_from;

  /**
   * @var Program the parent Program
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\Program",
   *     inversedBy="remix_notification_mentions_as_parent"
   * )
   * @ORM\JoinColumn(
   *     name="program_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private $program;

  /**
   * @var Program the newly remixed child Program
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\Program",
   *     inversedBy="remix_notification_mentions_as_child"
   * )
   * @ORM\JoinColumn(
   *     name="remix_program_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private $remix_program;

  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = '/Notifications/NotificationTypes/remix_notification.html.twig';

  /**
   * RemixNotification constructor.
   *
   * @param User    $user          the User to which this RemixNotification will be shown
   * @param User    $remix_from    the owner of the parent Program
   * @param Program $program       the parent Program
   * @param Program $remix_program the newly remixed child Program
   */
  public function __construct(User $user, User $remix_from, Program $program, Program $remix_program)
  {
    parent::__construct($user);
    $this->remix_from = $remix_from;
    $this->program = $program;
    $this->remix_program = $remix_program;
  }

  /**
   * Returns the owner of the parent Program.
   *
   * @return User the owner of the parent Program
   */
  public function getRemixFrom()
  {
    return $this->remix_from;
  }

  /**
   * Sets the owner of the parent Program.
   *
   * @param User $remix_from the owner of the parent Program
   */
  public function setRemixFrom(User $remix_from)
  {
    $this->remix_from = $remix_from;
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
   * Returns the parent Program.
   *
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * Sets the parent Program.
   *
   * @param Program $program the parent Program
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }

  /**
   * Returns the child Program.
   *
   * @return Program
   */
  public function getRemixProgram()
  {
    return $this->remix_program;
  }

  /**
   * Sets the child Program.
   *
   * @param Program $remix_program the child Program
   */
  public function setRemixProgram($remix_program)
  {
    $this->remix_program = $remix_program;
  }
}
