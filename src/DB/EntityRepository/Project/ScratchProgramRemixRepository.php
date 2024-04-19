<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScratchProgramRemixRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProgramRemixRelation::class);
  }

  /**
   * @param string[] $program_ids
   */
  public function getDirectEdgeRelationsOfProgramIds(array $program_ids): array
  {
    $qb = $this->createQueryBuilder('s');

    return $qb
      ->select('s')
      ->where('s.catrobat_child_id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @param string[] $scratch_parent_program_ids
   */
  public function removeParentRelations(string $program_id, array $scratch_parent_program_ids): void
  {
    $qb = $this->createQueryBuilder('s');

    $qb
      ->delete()
      ->where('s.scratch_parent_id IN (:scratch_parent_program_ids)')
      ->andWhere($qb->expr()->eq('s.catrobat_child_id', ':program_id'))
      ->setParameter('scratch_parent_program_ids', $scratch_parent_program_ids)
      ->setParameter('program_id', $program_id)
      ->getQuery()
      ->execute()
    ;
  }

  public function removeAllRelations(): void
  {
    $qb = $this->createQueryBuilder('s');

    $qb
      ->delete()
      ->getQuery()
      ->execute()
    ;
  }
}
