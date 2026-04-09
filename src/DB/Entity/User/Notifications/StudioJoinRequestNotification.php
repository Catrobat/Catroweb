<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StudioJoinRequestNotification extends CatroNotification
{
  private string $twig_template = 'User/Notification/Type/StudioJoinRequest.html.twig';

  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'studio_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Studio::class)]
    private ?Studio $studio,
    #[ORM\JoinColumn(name: 'admin_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $admin_user,
    #[ORM\Column(name: 'join_request_action', type: 'string', length: 20, nullable: true)]
    private ?string $action = null,
  ) {
    parent::__construct($user, '', '', 'studio');
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function getAdminUser(): ?User
  {
    return $this->admin_user;
  }

  public function getAction(): ?string
  {
    return $this->action;
  }

  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
