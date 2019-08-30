<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserLikeSimilarityRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * Class UserLikeSimilarityRelationRepository
 * @package App\Repository
 */
class UserLikeSimilarityRelationRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserLikeSimilarityRelation::class);
  }

  /**
   *
   */
  public function removeAllUserRelations()
  {
    $qb = $this->createQueryBuilder('ul');

    $qb
      ->delete()
      ->getQuery()
      ->execute();
  }

  /**
   * @param User $user
   *
   * @return UserLikeSimilarityRelation[]
   */
  public function getRelationsOfSimilarUsers(User $user)
  {
    $qb = $this->createQueryBuilder('ul');

    return $qb
      ->select('ul')
      ->where($qb->expr()->eq('ul.first_user', ':user'))
      ->orWhere($qb->expr()->eq('ul.second_user', ':user'))
      ->orderBy('ul.similarity', 'DESC')
      ->setParameter('user', $user)
      ->distinct()
      ->getQuery()
      ->getResult();
  }
}
