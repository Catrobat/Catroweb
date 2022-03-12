<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Project\Remix\RemixManager;
use App\User\Notification\NotificationManager;
use Exception;

final class NotificationsApiProcessor extends AbstractApiProcessor
{
  private NotificationRepository $notification_repository;
  private NotificationManager $notification_manager;
  private RemixManager $remix_manager;

  public function __construct(
        NotificationRepository $notification_repository,
        NotificationManager $notification_manager,
        RemixManager $remix_manager
    ) {
    $this->notification_repository = $notification_repository;
    $this->notification_manager = $notification_manager;
    $this->remix_manager = $remix_manager;
  }

  /**
   * @throws Exception
   */
  public function markAllAsSeen(User $user): void
  {
    $notifications = $this->notification_repository->findBy(['user' => $user]);
    $notifications_seen = [];
    foreach ($notifications as $notification) {
      if (!$notification->getSeen()) {
        $notifications_seen[$notification->getID()] = $notification;
      }
    }
    $this->notification_manager->markSeen($notifications_seen);
    $this->remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);
  }
}
