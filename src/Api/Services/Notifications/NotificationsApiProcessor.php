<?php

declare(strict_types=1);

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\Notification\NotificationManager;

class NotificationsApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly NotificationRepository $notification_repository, private readonly NotificationManager $notification_manager)
  {
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

  public function markAllAsSeen(User $user): void
  {
    $this->notification_repository->markAllNotificationsFromUserAsSeen($user);
  }
}
