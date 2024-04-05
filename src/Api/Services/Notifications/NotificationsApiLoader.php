<?php

declare(strict_types=1);

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\EntityRepository\User\Notification\NotificationRepository;

class NotificationsApiLoader extends AbstractApiLoader
{
  public function __construct(private readonly NotificationRepository $notification_repository)
  {
  }

  public function findNotificationByID(int $id): ?object
  {
    return $this->notification_repository->find($id);
  }
}
