<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FollowNotification extends CatroNotification
{
  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = '/User/Notification/Type/Follow.html.twig';

  /**
   * FollowNotification constructor.
   *
   * @param User $user     the User to which this FollowNotification should be shown
   * @param User $follower the User which "follow action" to another user triggered this FollowNotification
   */
  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'follower_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'follow_notification_mentions')]
    private User $follower
  ) {
    parent::__construct($user, '', '', 'follow');
  }

  /**
   * Returns the User which "follow action" to another user triggered this FollowNotification.
   */
  public function getFollower(): User
  {
    return $this->follower;
  }

  /**
   * Sets the User which "follow action" to another user triggered this FollowNotification.
   */
  public function setFollower(User $follower): void
  {
    $this->follower = $follower;
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
