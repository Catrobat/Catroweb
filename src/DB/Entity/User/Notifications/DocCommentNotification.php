<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/** Tells a docs maintainer about a new comment on a docs page; it goes with the comment when that is deleted. */
#[ORM\Entity]
class DocCommentNotification extends CatroNotification
{
  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'doc_comment_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: UserComment::class)]
    private ?UserComment $comment,
  ) {
    parent::__construct($user, '', '', 'comment');
  }

  public function getComment(): ?UserComment
  {
    return $this->comment;
  }
}
