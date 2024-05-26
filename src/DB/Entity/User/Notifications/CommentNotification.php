<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CommentNotification extends CatroNotification
{
  /*
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'Notifications/NotificationTypes/comment_notification.html.twig';

  /**
   * CommentNotification constructor.
   *
   * @param User             $user    the user to which this CommentNotification should be shown
   * @param UserComment|null $comment the UserComment which triggered this CommentNotification
   */
  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'comment_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: UserComment::class, inversedBy: 'notification')]
    private ?UserComment $comment
  ) {
    parent::__construct($user, '', '', 'comment');
  }

  /**
   * Returns the UserComment which triggered this CommentNotification.
   */
  public function getComment(): ?UserComment
  {
    return $this->comment;
  }

  /**
   * Sets the UserComment which triggered this CommentNotification.
   */
  public function setComment(UserComment $comment): void
  {
    $this->comment = $comment;
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
