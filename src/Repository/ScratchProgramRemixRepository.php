<?php

namespace App\Repository;

use App\Entity\ScratchProgramRemixRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\GuidType;

/**
 * Class ScratchProgramRemixRepository
 * @package App\Repository
 */
class ScratchProgramRemixRepository extends ServiceEntityRepository
{

  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProgramRemixRelation::class);
  }

  /**
   * @param int[] $program_ids
   *
   * @return int[]
   */
  public function getDirectEdgeRelationsOfProgramIds(array $program_ids)
  {
    $qb = $this->createQueryBuilder('s');

    return $qb
      ->select('s')
      ->where('s.catrobat_child_id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult();
  }

  /**
   * @param GuidType   $program_id
   * @param int[] $scratch_parent_program_ids
   */
  public function removeParentRelations($program_id, array $scratch_parent_program_ids)
  {
    $qb = $this->createQueryBuilder('s');

    $qb
      ->delete()
      ->where('s.scratch_parent_id IN (:scratch_parent_program_ids)')
      ->andWhere($qb->expr()->eq('s.catrobat_child_id', ':program_id'))
      ->setParameter('scratch_parent_program_ids', $scratch_parent_program_ids)
      ->setParameter('program_id', $program_id)
      ->getQuery()
      ->execute();
  }

  /**
   *
   */
  public function removeAllRelations()
  {
    $qb = $this->createQueryBuilder('s');

    $qb
      ->delete()
      ->getQuery()
      ->execute();
  }
}
