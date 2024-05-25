<?php

declare(strict_types=1);

namespace App\User\Notification;

use Doctrine\ORM\EntityManagerInterface;

class NotificationManager
{
  public function __construct(private readonly EntityManagerInterface $em)
  {
  }

  public function addNotification(mixed $notification, bool $flush = true): void
  {
    $this->em->persist($notification);
    if ($flush) {
      $this->em->flush();
    }
  }

  public function addNotifications(mixed $notifications): void
  {
    foreach ($notifications as $notification) {
      $this->em->persist($notification);
    }

    $this->em->flush();
  }

  public function removeNotification(mixed $notification): void
  {
    $this->em->remove($notification);
    $this->em->flush();
  }

  public function markSeen(mixed $notifications): void
  {
    foreach ($notifications as $notification) {
      $notification->setSeen(true);
      $this->em->persist($notification);
    }

    $this->em->flush();
  }

  public function deleteNotifications(mixed $notifications): void
  {
    foreach ($notifications as $notification) {
      $this->em->remove($notification);
    }

    $this->em->flush();
  }
}
