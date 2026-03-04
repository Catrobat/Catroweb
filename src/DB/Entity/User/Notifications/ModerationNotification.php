<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ModerationNotification extends CatroNotification
{
  private string $twig_template = 'User/Notification/Type/Moderation.html.twig';

  public function __construct(
    User $user,
    #[ORM\Column(name: 'moderation_content_type', type: Types::STRING, length: 20, nullable: true)]
    private ?string $content_type = null,
    #[ORM\Column(name: 'moderation_content_id', type: Types::STRING, length: 255, nullable: true)]
    private ?string $content_id = null,
    #[ORM\Column(name: 'moderation_action', type: Types::STRING, length: 30, nullable: true)]
    private ?string $moderation_action = null,
    string $message = '',
  ) {
    parent::__construct($user, '', $message, 'moderation');
  }

  public function getContentType(): ?string
  {
    return $this->content_type;
  }

  public function setContentType(?string $content_type): void
  {
    $this->content_type = $content_type;
  }

  public function getContentId(): ?string
  {
    return $this->content_id;
  }

  public function setContentId(?string $content_id): void
  {
    $this->content_id = $content_id;
  }

  public function getModerationAction(): ?string
  {
    return $this->moderation_action;
  }

  public function setModerationAction(?string $moderation_action): void
  {
    $this->moderation_action = $moderation_action;
  }

  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
