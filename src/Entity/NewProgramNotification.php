<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NewProgramNotification extends CatroNotification
{
  /**
   * The new Program which triggered this NewProgramNotification. If this Program gets deleted,
   * this NewProgramNotification gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\Program",
   *     inversedBy="new_program_notification_mentions"
   * )
   * @ORM\JoinColumn(
   *     name="program_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private ?Program $program;

  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'Notifications/NotificationTypes/new_program_notification.html.twig';

  public function __construct(User $user, ?Program $program)
  {
    parent::__construct($user, '', '', 'follow');
    $this->program = $program;
  }

  /**
   * Returns the new Program which triggered this NewProgramNotification.
   */
  public function getProgram(): ?Program
  {
    return $this->program;
  }

  /**
   * Sets the new Program which triggered this NewProgramNotification.
   */
  public function setProgram(?Program $program): void
  {
    $this->program = $program;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
