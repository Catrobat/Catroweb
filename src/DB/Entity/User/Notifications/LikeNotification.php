<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LikeNotification extends CatroNotification
{
  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = '/User/Notification/Type/Like.html.twig';

  /**
   * LikeNotification constructor.
   *
   * @param User         $user      the User to which this LikeNotification will be shown
   * @param User|null    $like_from the User which "like action" to another user triggered this LikeNotification
   * @param Program|null $program   the Program to which the ProgramLike and this LikeNotification is notifying, belongs to
   */
  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'like_from', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'like_notification_mentions')]
    private ?User $like_from,
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'like_notification_mentions')]
    private ?Program $program
  ) {
    parent::__construct($user, '', '', 'reaction');
  }

  /**
   * Returns the User which "like action" to another user triggered this LikeNotification.
   */
  public function getLikeFrom(): ?User
  {
    return $this->like_from;
  }

  /**
   * Sets the User which "like action" to another user triggered this LikeNotification.
   */
  public function setLikeFrom(?User $like_from): void
  {
    $this->like_from = $like_from;
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

  /**
   * Returns the Program to which the ProgramLike and this LikeNotification is notifying, belongs to.
   */
  public function getProgram(): ?Program
  {
    return $this->program;
  }

  /**
   * Sets the Program to which the ProgramLike and this LikeNotification is notifying, belongs to.
   */
  public function setProgram(?Program $program): void
  {
    $this->program = $program;
  }
}
