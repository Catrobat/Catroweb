<?php

declare(strict_types=1);

namespace App\Api\Services\Followers;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\Notification\NotificationManager;
use App\User\UserManager;

class FollowersApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly NotificationManager $notification_manager,
    private readonly NotificationRepository $notification_repo,
  ) {
  }

  public function followUser(User $follower, User $user_to_follow): void
  {
    $follower->addFollowing($user_to_follow);
    $this->user_manager->updateUser($follower);
    $this->addFollowNotificationIfNotExists($follower, $user_to_follow);
  }

  public function unfollowUser(User $follower, User $user_to_unfollow): void
  {
    $follower->removeFollowing($user_to_unfollow);
    $this->user_manager->updateUser($follower);

    $existing_notifications = $this->notification_repo->getFollowNotificationForUser($user_to_unfollow, $follower);
    foreach ($existing_notifications as $notification) {
      $this->notification_manager->removeNotification($notification);
    }
  }

  private function addFollowNotificationIfNotExists(User $follower, User $user_to_follow): void
  {
    $existing = $this->notification_repo->getFollowNotificationForUser($user_to_follow, $follower);
    if ([] !== $existing) {
      return;
    }

    $notification = new FollowNotification($user_to_follow, $follower);
    $this->notification_manager->addNotification($notification);
  }
}
