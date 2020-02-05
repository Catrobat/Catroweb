<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NewProgramNotification extends CatroNotification
{

  /**
   * @var Program The new Program which triggered this NewProgramNotification. If this Program gets deleted,
   *              this NewProgramNotification gets deleted as well.
   *
   * @ORM\ManyToOne(
   *   targetEntity="\App\Entity\Program",
   *   inversedBy="new_program_notification_mentions"
   * )
   * @ORM\JoinColumn(
   *   name="program_id",
   *   referencedColumnName="id",
   *   nullable=true
   *  )
   */
  private $program;

  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = "Notifications/NotificationTypes/new_program_notification.html.twig";

  /**
   * NewProgramNotification constructor.
   *
   * @param User $user
   * @param      $program
   *
   */
  public function __construct(User $user, $program)
  {
    parent::__construct($user);
    $this->program = $program;
  }

  /**
   * Returns the new Program which triggered this NewProgramNotification.
   *
   * @return Program The new Program which triggered this NewProgramNotification.
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * Sets the new Program which triggered this NewProgramNotification.
   *
   * @param Program $program The new Program which triggered this NewProgramNotification.
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