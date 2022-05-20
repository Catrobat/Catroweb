<?php

namespace App\User\Notification;

use Doctrine\ORM\EntityManagerInterface;

class NotificationManager
{
  public function __construct(private readonly EntityManagerInterface $em)
  {
  }

  /**
   * @param mixed $notification
   */
  public function addNotification($notification): void
  {
    $this->em->persist($notification);
    $this->em->flush();
  }

  /**
   * @param mixed $notifications
   */
  public function addNotifications($notifications): void
  {
    foreach ($notifications as $notification) {
      $this->em->persist($notification);
    }
    $this->em->flush();
  }

  /**
   * @param mixed $notification
   */
  public function removeNotification($notification): void
  {
    $this->em->remove($notification);
    $this->em->flush();
  }

  /**
   * @param mixed $notifications
   */
  public function markSeen($notifications): void
  {
    foreach ($notifications as $notification) {
      $notification->setSeen(true);
      $this->em->persist($notification);
    }
    $this->em->flush();
  }

  /**
   * @param mixed $notifications
   */
  public function deleteNotifications($notifications): void
  {
    foreach ($notifications as $notification) {
      $this->em->remove($notification);
    }
    $this->em->flush();
  }
}
