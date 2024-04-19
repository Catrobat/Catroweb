<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\RecommenderSystem;

use App\DB\Entity\User\RecommenderSystem\UserLikeSimilarityRelation;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserLikeSimilarityRelationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserLikeSimilarityRelation::class);
  }

  public function removeAllUserRelations(): void
  {
    $qb = $this->createQueryBuilder('ul');

    $qb
      ->delete()
      ->getQuery()
      ->execute()
    ;
  }

  /**
   * @return UserLikeSimilarityRelation[]
   */
  public function getRelationsOfSimilarUsers(User $user): array
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
      ->getResult()
    ;
  }
}
