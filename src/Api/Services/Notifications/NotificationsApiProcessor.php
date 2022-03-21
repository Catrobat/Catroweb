<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\Notification\NotificationManager;
use Exception;

final class NotificationsApiProcessor extends AbstractApiProcessor
{
  private NotificationRepository $notification_repository;
  private NotificationManager $notification_manager;

  public function __construct(
        NotificationRepository $notification_repository,
        NotificationManager $notification_manager
    ) {
    $this->notification_repository = $notification_repository;
    $this->notification_manager = $notification_manager;
  }

  public function markNotificationAsSeen(int $notification_id, User $user): bool
  {
    $notification_seen = $this->notification_repository->findOneBy(['id' => $notification_id, 'user' => $user]);
    if (null === $notification_seen) {
      return false;
    }
    $this->notification_manager->markSeen([$notification_seen]);

    return true;
  }

  /**
   * @throws Exception
   */
  public function markAllAsSeen(User $user): void
  {
    $this->notification_repository->markAllNotificationsFromUserAsSeen($user);
  }
}
