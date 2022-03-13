<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationsApiLoader extends AbstractApiLoader
{
  private EntityManagerInterface $entity_manager;
  private NotificationManager $notification_manager;

  public function __construct(EntityManagerInterface $entity_manager, NotificationManager $notification_manager)
  {
    $this->entity_manager = $entity_manager;
    $this->notification_manager = $notification_manager;
  }

    public function findNotificationByID(string $id)
    {
        /*return $this->notification_manager->find($id);*/
        print $this->notification_manager->findOneBy(['id' => $id]);
        return $this->notification_manager->findOneBy(['id' => $id]);
    }
}
