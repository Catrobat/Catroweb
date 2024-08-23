<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class NewProgramNotification extends CatroNotification
{
  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'User/Notification/Type/NewProject.html.twig';

  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'new_program_notification_mentions')]
    private ?Program $program
  ) {
    parent::__construct($user, '', '', 'follow');
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
   * It's important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
