<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Notification;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, CatroNotification::class);
  }

  /**
   * @return LikeNotification[]
   */
  public function getLikeNotificationsForProject(Program $project, ?User $owner = null, ?User $likeFrom = null): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->select('n')
      ->from(LikeNotification::class, 'n')
      ->where($qb->expr()->eq('n.program', ':program_id'))
      ->setParameter(':program_id', $project->getId())
    ;

    if ($owner instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if ($likeFrom instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.like_from', ':like_from_id'))
        ->setParameter(':like_from_id', $likeFrom->getId())
      ;
    }

    return $qb->getQuery()->getResult();
  }

  /**
   * @return FollowNotification[]
   */
  public function getFollowNotificationForUser(?User $owner = null, ?User $follow_from = null): array
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->select('n')
      ->from(FollowNotification::class, 'n')
    ;

    if ($owner instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if ($follow_from instanceof User) {
      $qb
        ->andWhere($qb->expr()->eq('n.follower', ':follower_id'))
        ->setParameter(':follower_id', $follow_from->getId())
      ;
    }

    return $qb->getQuery()->getResult();
  }

  public function markAllNotificationsFromUserAsSeen(User $user): void
  {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb
      ->update(CatroNotification::class, 'n')
      ->set('n.seen', ':seen')
      ->where($qb->expr()->eq('n.user', ':user'))
      ->andWhere($qb->expr()->eq('n.seen', ':unseen'))
      ->setParameter('seen', true)
      ->setParameter('user', $user)
      ->setParameter('unseen', false)
      ->getQuery()
      ->execute()
    ;
  }
}
