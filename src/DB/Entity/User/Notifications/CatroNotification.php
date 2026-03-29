<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Generic Notification.
 */
#[ORM\Table(name: 'CatroNotification')]
#[ORM\Index(name: 'notif_user_seen_idx', columns: ['user', 'seen'])]
#[ORM\Index(name: 'notif_user_id_idx', columns: ['user', 'id'])]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'notification_type', type: 'string')]
#[ORM\DiscriminatorMap(['default' => 'CatroNotification', 'comment' => 'CommentNotification', 'like' => 'LikeNotification', 'follow' => 'FollowNotification', 'follow_project' => 'NewProgramNotification', 'broadcast_notification' => 'BroadcastNotification', 'remix_notification' => 'RemixNotification', 'moderation' => 'ModerationNotification'])]
class CatroNotification
{
  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  private ?int $id = null;

  #[ORM\Column(name: 'seen', type: Types::BOOLEAN, options: ['default' => false])]
  private bool $seen = false;

  #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
  private \DateTimeInterface $createdAt;

  #[ORM\Column(name: 'email_sent', type: Types::BOOLEAN, options: ['default' => false])]
  private bool $emailSent = false;

  private string $twig_template = 'User/Notification/Type/Catro.html.twig';

  public function __construct(
    /**
     *  The user to which this CatroNotification will be shown.
     *  If the user gets deleted, this CatroNotification gets deleted as well.
     */
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    private User $user,
    #[ORM\Column(name: 'title', type: Types::STRING)]
    private string $title = '',
    #[ORM\Column(name: 'message', type: Types::TEXT)]
    private string $message = '',
    #[ORM\Column(name: 'type', type: Types::STRING)]
    private string $type = '',
  ) {
    $this->createdAt = new \DateTime();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function setTitle(string $title): CatroNotification
  {
    $this->title = $title;

    return $this;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function setSeen(bool $seen): CatroNotification
  {
    $this->seen = $seen;

    return $this;
  }

  public function getSeen(): bool
  {
    return $this->seen;
  }

  public function setMessage(string $message): CatroNotification
  {
    $this->message = $message;

    return $this;
  }

  public function getMessage(): string
  {
    return $this->message;
  }

  /**
   * Sets the user to which this CatroNotification will be shown.
   */
  public function setUser(User $user): CatroNotification
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Returns the user to which this CatroNotification will be shown.
   */
  public function getUser(): User
  {
    return $this->user;
  }

  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }

  public function setTwigTemplate(string $twig_template): void
  {
    $this->twig_template = $twig_template;
  }

  public function setType(string $type): CatroNotification
  {
    $this->type = $type;

    return $this;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getCreatedAt(): \DateTimeInterface
  {
    return $this->createdAt;
  }

  public function setCreatedAt(\DateTimeInterface $createdAt): void
  {
    $this->createdAt = $createdAt;
  }

  public function isEmailSent(): bool
  {
    return $this->emailSent;
  }

  public function setEmailSent(bool $emailSent): void
  {
    $this->emailSent = $emailSent;
  }
}
