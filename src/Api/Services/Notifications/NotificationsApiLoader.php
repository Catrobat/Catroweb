<?php

declare(strict_types=1);

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;

class NotificationsApiLoader extends AbstractApiLoader
{
  public function __construct(private readonly NotificationRepository $notification_repository)
  {
  }

  public function findNotificationByID(string $id): ?object
  {
    return $this->notification_repository->find($id);
  }

  /**
   * @return array{notifications: CatroNotification[], has_more: bool}
   */
  public function getNotificationsPage(User $user, string $type, int $limit, ?string $cursor_id): array
  {
    return $this->notification_repository->getNotificationsPageData($user, $type, $limit, $cursor_id);
  }
}
