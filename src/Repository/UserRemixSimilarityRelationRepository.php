<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserRemixSimilarityRelation;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Exception;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * Class UserRemixSimilarityRelationRepository
 * @package App\Repository
 */
class UserRemixSimilarityRelationRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserRemixSimilarityRelation::class);
  }

  /**
   *
   */
  public function removeAllUserRelations()
  {
    $qb = $this->createQueryBuilder('ur');

    $qb
      ->delete()
      ->getQuery()
      ->execute();
  }

  /**
   * @param User $user
   *
   * @return UserRemixSimilarityRelation[]
   */
  public function getRelationsOfSimilarUsers(User $user)
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
      ->getResult();
  }

  /**
   * @param $first_user_id
   * @param $second_user_id
   * @param $similarity
   *
   * @throws DBALException
   * @throws Exception
   */
  public function insertRelation($first_user_id, $second_user_id, $similarity)
  {
    $connection = $this->getEntityManager()->getConnection();
    $connection->insert('user_remix_similarity_relation', [
      'first_user_id'  => $first_user_id,
      'second_user_id' => $second_user_id,
      'similarity'     => $similarity,
      'created_at'     => date_format(new DateTime(), "Y-m-d H:i:s"),
    ]);
  }
}
