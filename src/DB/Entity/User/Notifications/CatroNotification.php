<?php

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Generic Notification.
 */
#[ORM\Table]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'notification_type', type: 'string')]
#[ORM\DiscriminatorMap(['default' => 'CatroNotification', 'comment' => 'CommentNotification', 'like' => 'LikeNotification', 'follow' => 'FollowNotification', 'follow_project' => 'NewProgramNotification', 'broadcast_notification' => 'BroadcastNotification', 'remix_notification' => 'RemixNotification'])]
class CatroNotification
{
  #[ORM\Column(name: 'id', type: 'integer')]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  private ?int $id = null;

  #[ORM\Column(name: 'seen', type: 'boolean', options: ['default' => false])]
  private bool $seen = false;

  private string $twig_template = 'Notifications/NotificationTypes/catro_notification.html.twig';

  public function __construct(
    /**
     *  The user to which this CatroNotification will be shown.
     *  If the user gets deleted, this CatroNotification gets deleted as well.
     */
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    private User $user,
    #[ORM\Column(name: 'title', type: 'string')]
    private string $title = '',
    #[ORM\Column(name: 'message', type: 'text')]
    private string $message = '',
    #[ORM\Column(name: 'type', type: 'string')]
    private string $type = ''
  ) {
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
   * Sets he user to which this CatroNotification will be shown.
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
}
