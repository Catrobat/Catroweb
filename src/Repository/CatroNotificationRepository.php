<?php

namespace App\Repository;

use App\Entity\CatroNotification;
use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\Program;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CatroNotificationRepository extends ServiceEntityRepository
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

    if (null !== $owner)
    {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if (null !== $likeFrom)
    {
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

    if (null !== $owner)
    {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId())
      ;
    }

    if (null !== $follow_from)
    {
      $qb
        ->andWhere($qb->expr()->eq('n.follower', ':follower_id'))
        ->setParameter(':follower_id', $follow_from->getId())
      ;
    }

    return $qb->getQuery()->getResult();
  }
}
