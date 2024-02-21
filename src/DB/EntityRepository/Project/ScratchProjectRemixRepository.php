<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Scratch\ScratchProjectRemixRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScratchProjectRemixRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProjectRemixRelation::class);
  }

  /**
   * @param string[] $project_ids
   */
  public function getDirectEdgeRelationsOfProjectIds(array $project_ids): array
  {
    $qb = $this->createQueryBuilder('s');

    return $qb
      ->select('s')
      ->where('s.catrobat_child_id IN (:project_ids)')
      ->setParameter('project_ids', $project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @param string[] $scratch_parent_project_ids
   */
  public function removeParentRelations(string $project_id, array $scratch_parent_project_ids): void
  {
    $qb = $this->createQueryBuilder('s');

    $qb
      ->delete()
      ->where('s.scratch_parent_id IN (:scratch_parent_project_ids)')
      ->andWhere($qb->expr()->eq('s.catrobat_child_id', ':project_id'))
      ->setParameter('scratch_parent_project_ids', $scratch_parent_project_ids)
      ->setParameter('project_id', $project_id)
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
