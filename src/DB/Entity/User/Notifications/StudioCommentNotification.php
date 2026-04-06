<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StudioCommentNotification extends CatroNotification
{
  private string $twig_template = 'User/Notification/Type/StudioComment.html.twig';

  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'studio_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Studio::class)]
    private ?Studio $studio,
    #[ORM\JoinColumn(name: 'comment_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $comment_user,
  ) {
    parent::__construct($user, '', '', 'studio');
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function setStudio(?Studio $studio): void
  {
    $this->studio = $studio;
  }

  public function getCommentUser(): ?User
  {
    return $this->comment_user;
  }

  public function setCommentUser(?User $comment_user): void
  {
    $this->comment_user = $comment_user;
  }

  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
