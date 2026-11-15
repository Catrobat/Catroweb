<?php

declare(strict_types=1);

namespace App\User\Notification;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\Notifications\NewProjectNotification;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * One query for the follower ids and one flush per batch. The followers collection is EXTRA_LAZY, so walking it
 * cost a query per follower, plus a flush per notification.
 */
#[AsMessageHandler]
final readonly class NotifyFollowersOfNewProjectHandler
{
  private const int BATCH = 200;

  public function __construct(private EntityManagerInterface $entity_manager)
  {
  }

  public function __invoke(NotifyFollowersOfNewProject $message): void
  {
    /** @var list<string> $follower_ids */
    $follower_ids = $this->entity_manager->createQuery(
      'SELECT f.id FROM '.Project::class.' p JOIN p.user u JOIN u.followers f WHERE p.id = :project'
    )->setParameter('project', $message->project_id)->getSingleColumnResult();

    foreach (array_chunk($follower_ids, self::BATCH) as $batch) {
      $project = $this->entity_manager->getReference(Project::class, $message->project_id);
      $notifications = [];
      foreach ($batch as $follower_id) {
        $follower = $this->entity_manager->getReference(User::class, $follower_id);
        if (null === $follower) {
          continue;
        }

        $notifications[] = $notification = new NewProjectNotification($follower, $project);
        $this->entity_manager->persist($notification);
      }

      $this->entity_manager->flush();
      // Not clear(): with the sync transport (tests) this runs inside the upload, whose entities must stay managed.
      foreach ($notifications as $notification) {
        $this->entity_manager->detach($notification);
      }
    }
  }
}
