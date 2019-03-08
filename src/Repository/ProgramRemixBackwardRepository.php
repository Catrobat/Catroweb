<?php

namespace App\Repository;

use App\Entity\ProgramRemixBackwardRelation;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;


/**
 * Class ProgramRemixBackwardRepository
 * @package App\Repository
 */
class ProgramRemixBackwardRepository extends EntityRepository
{
  /**
   * @param int[] $program_ids
   *
   * @return ProgramRemixBackwardRelation[]
   */
  public function getParentRelations(array $program_ids)
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->where('b.child_id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult();
  }

  /**
   * @param array $edge_start_program_ids
   * @param array $edge_end_program_ids
   *
   * @return ProgramRemixBackwardRelation[]
   */
  public function getDirectEdgeRelations(array $edge_start_program_ids, array $edge_end_program_ids)
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->where('b.parent_id IN (:edge_start_program_ids)')
      ->andWhere('b.child_id IN (:edge_end_program_ids)')
      ->setParameter('edge_start_program_ids', $edge_start_program_ids)
      ->setParameter('edge_end_program_ids', $edge_end_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult();
  }

  /**
   * @param int   $program_id
   * @param int[] $parent_program_ids
   */
  public function removeParentRelations($program_id, array $parent_program_ids)
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->delete()
      ->where('b.parent_id IN (:parent_program_ids)')
      ->andWhere($qb->expr()->eq('b.child_id', ':program_id'))
      ->setParameter('parent_program_ids', $parent_program_ids)
      ->setParameter('program_id', $program_id)
      ->getQuery()
      ->execute();
  }

  /**
   *
   */
  public function removeAllRelations()
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->delete()
      ->getQuery()
      ->execute();
  }

  /**
   * @param User $user
   *
   * @return ProgramRemixBackwardRelation[]
   */
  public function getUnseenChildRelationsOfUser(User $user)
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->innerJoin('b.parent', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'b.parent_id = p.id')
      ->innerJoin('b.child', 'p2', \Doctrine\ORM\Query\Expr\Join::WITH, 'b.child_id = p2.id')
      ->where($qb->expr()->eq('p.user', ':user'))
      ->andWhere($qb->expr()->neq('p2.user', 'p.user'))
      ->andWhere($qb->expr()->isNull('b.seen_at'))
      ->orderBy('b.created_at', 'DESC')
      ->setParameter('user', $user)
      ->distinct()
      ->getQuery()
      ->getResult();
  }

  /**
   * @param \DateTime $seen_at
   */
  public function markAllUnseenRelationsAsSeen(\DateTime $seen_at)
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->update()
      ->set('b.seen_at', ':seen_at')
      ->setParameter(':seen_at', $seen_at)
      ->getQuery()
      ->execute();
  }

  /**
   * @param int $program_id
   *
   * @return int
   */
  public function remixCount($program_id)
  {
    $qb = $this->createQueryBuilder('b');

    $result = $qb
      ->select('b')
      ->where($qb->expr()->eq('b.parent_id', ':program_id'))
      ->setParameter('program_id', $program_id)
      ->distinct()
      ->getQuery()
      ->getResult();

    return count($result);
  }
}
