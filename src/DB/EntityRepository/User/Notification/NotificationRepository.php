<?php

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
  public function getLikeNotificationsForProject(Program $project, User $owner = null, User $likeFrom = null): array
  {
    $qb = $this->_em->createQueryBuilder();

    $qb
      ->select('n')
      ->from(LikeNotification::class, 'n')
      ->where($qb->expr()->eq('n.program', ':program_id'))
      ->setParameter(':program_id', $project->getId())
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

  public function getNotificationByID(int $id): ?CatroNotification
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->where($query_builder->expr()->eq('e.id', $id))
        ;

    return $query_builder->getQuery()->getResult();
  }
}
