<?php

namespace App\DB\EntityRepository\User\Notification;

use App\DB\Entity\Project\Project;
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
  public function getLikeNotificationsForProject(Project $project, User $owner = null, User $likeFrom = null): array
  {
    $qb = $this->_em->createQueryBuilder();

    $qb
      ->select('n')
      ->from(LikeNotification::class, 'n')
      ->where($qb->expr()->eq('n.project', ':project_id'))
      ->setParameter(':project_id', $project->getId())
    ;

    if (null !== $owner) {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if (null !== $likeFrom) {
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
  public function getFollowNotificationForUser(User $owner = null, User $follow_from = null): array
  {
    $qb = $this->_em->createQueryBuilder();

    $qb
      ->select('n')
      ->from(FollowNotification::class, 'n')
    ;

    if (null !== $owner) {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if (null !== $follow_from) {
      $qb
        ->andWhere($qb->expr()->eq('n.follower', ':follower_id'))
        ->setParameter(':follower_id', $follow_from->getId())
      ;
    }

    return $qb->getQuery()->getResult();
  }

  public function markAllNotificationsFromUserAsSeen(User $user): void
  {
    $qb = $this->_em->createQueryBuilder();

    $qb
      ->select('n')
      ->from(CatroNotification::class, 'n')
      ->andWhere($qb->expr()->eq('n.user', ':user_id'))
      ->setParameter(':user_id', $user->getId())
      ->andWhere($qb->expr()->eq('n.seen', 0))
    ;

    $unseen_notifications = $qb->getQuery()->getResult();

    foreach ($unseen_notifications as $unseen_notification) {
      $unseen_notification->setSeen(1);
      $this->getEntityManager()->persist($unseen_notification);
    }

    $this->getEntityManager()->flush();
  }
}
