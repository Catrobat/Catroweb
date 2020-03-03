<?php

namespace App\Repository;

use App\Entity\CatroNotification;
use App\Entity\LikeNotification;
use App\Entity\Program;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CatroNotificationRepository
 * @package App\Entity
 */
class CatroNotificationRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, CatroNotification::class);
  }

  /**
   * @param Program   $project
   * @param User|null $owner
   * @param User|null $likeFrom
   *
   * @return LikeNotification[]
   */
  public function getLikeNotificationsForProject(Program $project, User $owner = null, User $likeFrom = null)
  {
    $qb = $this->_em->createQueryBuilder();

    $qb
      ->select('n')
      ->from('\App\Entity\LikeNotification', 'n')
      ->where($qb->expr()->eq('n.program', ':program_id'))
      ->setParameter(':program_id', $project->getId());

    if ($owner !== null)
    {
      $qb
        ->andWhere($qb->expr()->eq('n.user', ':user_id'))
        ->setParameter(':user_id', $owner->getId());
    }

    if ($likeFrom !== null)
    {
      $qb
        ->andWhere($qb->expr()->eq('n.like_from', ':like_from_id'))
        ->setParameter(':like_from_id', $likeFrom->getId());
    }

    return $qb->getQuery()->getResult();
  }
}
