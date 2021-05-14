<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Generic Notification.
 *
 * @ORM\Table
 */

/**
 * @ORM\Entity(repositoryClass="App\Repository\CatroNotificationRepository")
 * @ORM\Table
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "default": "CatroNotification",
 *     "comment": "CommentNotification",
 *     "like": "LikeNotification",
 *     "follow": "FollowNotification",
 *     "follow_program": "NewProgramNotification",
 *     "broadcast_notification": "BroadcastNotification",
 *     "remix_notification": "RemixNotification"
 * })
 */
class CatroNotification
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private ?int $id = null;

  /**
   *  The user to which this CatroNotification will be shown.
   *  If the user gets deleted, this CatroNotification gets deleted as well.
   *
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="notifications")
   * @ORM\JoinColumn(
   *     name="user",
   *     referencedColumnName="id",
   *     nullable=false
   * )
   */
  private User $user;

  /**
   * @ORM\Column(name="title", type="string")
   */
  private string $title = '';

  /**
   * @ORM\Column(name="message", type="text")
   */
  private string $message = '';

  /**
   * @ORM\Column(name="seen", type="boolean", options={"default": false})
   */
  private bool $seen = false;

  /**
   * @ORM\Column(name="type", type="string")
   */
  private string $type = '';

  private string $twig_template = 'Notifications/NotificationTypes/catro_notification.html.twig';

  public function __construct(User $user, string $title = '', string $message = '', string $type = '')
  {
    $this->user = $user;
    $this->title = $title;
    $this->message = $message;
    $this->type = $type;
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
