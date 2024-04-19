<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\RecommenderSystem;

use App\DB\Entity\User\RecommenderSystem\UserRemixSimilarityRelation;
use App\DB\Entity\User\User;
use App\Utils\TimeUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class UserRemixSimilarityRelationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserRemixSimilarityRelation::class);
  }

  public function removeAllUserRelations(): void
  {
    $qb = $this->createQueryBuilder('ur');

    $qb
      ->delete()
      ->getQuery()
      ->execute()
    ;
  }

  /**
   * @return UserRemixSimilarityRelation[]
   */
  public function getRelationsOfSimilarUsers(User $user): array
  {
    $qb = $this->createQueryBuilder('ur');

    return $qb
      ->select('ur')
      ->where($qb->expr()->eq('ur.first_user', ':user'))
      ->orWhere($qb->expr()->eq('ur.second_user', ':user'))
      ->orderBy('ur.similarity', 'DESC')
      ->setParameter('user', $user)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @throws Exception
   * @throws \Exception
   */
  public function insertRelation(string $first_user_id, string $second_user_id, float $similarity): void
  {
    $connection = $this->getEntityManager()->getConnection();
    $connection->insert('user_remix_similarity_relation', [
      'first_user_id' => $first_user_id,
      'second_user_id' => $second_user_id,
      'similarity' => $similarity,
      'created_at' => date_format(TimeUtils::getDateTime(), 'Y-m-d H:i:s'),
    ]);
  }
}
