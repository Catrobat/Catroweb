<?php

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RemixNotification extends CatroNotification
{
  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = '/Notifications/NotificationTypes/remix_notification.html.twig';

  /**
   * RemixNotification constructor.
   *
   * @param User         $user          the User to which this RemixNotification will be shown
   * @param User|null    $remix_from    the owner of the parent Program
   * @param Program|null $program       the parent Program
   * @param Program|null $remix_program the newly remixed child Program
   */
  public function __construct(User $user, /**
     * the owner of the parent Program.
     */
    #[ORM\JoinColumn(name: 'remix_root', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $remix_from, /**
     *  the parent Program.
     */
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'remix_notification_mentions_as_parent')]
    private ?Program $program, /**
     * the newly remixed child Program.
     */
    #[ORM\JoinColumn(name: 'remix_program_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'remix_notification_mentions_as_child')]
    private ?Program $remix_program)
  {
    parent::__construct($user, '', '', 'remix');
  }

  /**
   * Returns the owner of the parent Program.
   */
  public function getRemixFrom(): ?User
  {
    return $this->remix_from;
  }

  /**
   * Sets the owner of the parent Program.
   */
  public function setRemixFrom(?User $remix_from): void
  {
    $this->remix_from = $remix_from;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }

  /**
   * Returns the parent Program.
   */
  public function getProgram(): ?Program
  {
    return $this->program;
  }

  /**
   * Sets the parent Program.
   */
  public function setProgram(?Program $program): void
  {
    $this->program = $program;
  }

  /**
   * Returns the child Program.
   */
  public function getRemixProgram(): ?Program
  {
    return $this->remix_program;
  }

  /**
   * Sets the child Program.
   */
  public function setRemixProgram(?Program $remix_program): void
  {
    $this->remix_program = $remix_program;
  }
}
