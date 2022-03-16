<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationsApiLoader extends AbstractApiLoader
{
  private EntityManagerInterface $entity_manager;
  private NotificationManager $notification_manager;
  private NotificationRepository $notification_repository;

  public function __construct(EntityManagerInterface $entity_manager, NotificationManager $notification_manager, NotificationRepository $notification_repository)
  {
    $this->entity_manager = $entity_manager;
    $this->notification_manager = $notification_manager;
    $this->notification_repository = $notification_repository;
  }

  public function findNotificationByID(int $id): ?object
  {
    return $this->notification_repository->find($id);
  }
}
