<?php

declare(strict_types=1);

namespace App\User\Notification;

use App\DB\Entity\Project\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationManager
{
  public function __construct(private readonly EntityManagerInterface $em, private readonly MessageBusInterface $message_bus)
  {
  }

  /** A popular uploader has thousands of followers: their notifications are written on the default worker. */
  public function queueNewProjectNotifications(Project $project): void
  {
    $this->message_bus->dispatch(new NotifyFollowersOfNewProject((string) $project->getId()));
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
}
