<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;


/**
 * Class UserLikeSimilarityRelationRepository
 * @package App\Repository
 */
class UserLikeSimilarityRelationRepository extends EntityRepository
{
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
   * @return mixed
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
