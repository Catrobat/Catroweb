<?php

namespace App\Repository;

use App\Entity\ProgramLike;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;


/**
 * Class ProgramLikeRepository
 * @package App\Repository
 */
class ProgramLikeRepository extends EntityRepository
{
  /**
   * @param int $program_id
   * @param int $type
   *
   * @return int
   */
  public function likeTypeCount($program_id, $type)
  {
    $qb = $this->createQueryBuilder('l');

    $result = $qb
      ->select('l')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.type', ':type'))
      ->setParameter(':program_id', $program_id)
      ->setParameter(':type', $type)
      ->distinct()
      ->getQuery()
      ->getResult();

    return count($result);
  }

  /**
   * @param int $program_id
   *
   * @return int
   */
  public function totalLikeCount($program_id)
  {
    $qb = $this->createQueryBuilder('l');

    $result = $qb
      ->select('l')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->setParameter(':program_id', $program_id)
      ->distinct()
      ->getQuery()
      ->getResult();

    return count($result);
  }

  /**
   * @param $user_ids array
   * @param $exclude_user_id
   * @param $exclude_program_ids
   * @param $flavor
   *
   * @return ProgramLike[]
   */
  public function getLikesOfUsers($user_ids, $exclude_user_id, $exclude_program_ids, $flavor)
  {
    $qb = $this->createQueryBuilder('l');

    return $qb
      ->select('l')
      ->innerJoin('App\Entity\Program', 'p', Join::WITH, $qb->expr()->eq('p.id', 'l.program'))
      ->where($qb->expr()->in('l.user_id', ':user_ids'))
      ->andWhere($qb->expr()->neq('IDENTITY(p.user)', ':exclude_user_id'))
      ->andWhere($qb->expr()->notIn('p.id', ':exclude_program_ids'))
      ->andWhere($qb->expr()->eq('p.visible', $qb->expr()->literal(true)))
      ->andWhere($qb->expr()->eq('p.flavor', ':flavor'))
      ->andWhere($qb->expr()->eq('p.private', $qb->expr()->literal(false)))
      ->setParameter('user_ids', $user_ids)
      ->setParameter('exclude_user_id', $exclude_user_id)
      ->setParameter('exclude_program_ids', $exclude_program_ids)
      ->setParameter('flavor', $flavor)
      ->distinct()
      ->getQuery()
      ->getResult();
  }
}
