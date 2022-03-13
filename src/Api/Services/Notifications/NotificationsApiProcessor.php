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
    $this->notification_repository->markAllNotificationsFromUserAsSeen($user);
  }
}
