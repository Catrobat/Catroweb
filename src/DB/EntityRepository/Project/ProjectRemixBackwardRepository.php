<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Remix\ProjectRemixBackwardRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectRemixBackwardRelation>
 */
class ProjectRemixBackwardRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectRemixBackwardRelation::class);
  }

  /**
   * @return ProjectRemixBackwardRelation[]
   */
  public function getParentRelations(array $program_ids): array
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->where('b.child_id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @return ProjectRemixBackwardRelation[]
   */
  public function getDirectEdgeRelations(array $edge_start_program_ids, array $edge_end_program_ids): array
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
      ->getResult()
    ;
  }

  /**
   * @param string[] $parent_program_ids
   */
  public function removeParentRelations(string $program_id, array $parent_program_ids): void
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->delete()
      ->where('b.parent_id IN (:parent_program_ids)')
      ->andWhere($qb->expr()->eq('b.child_id', ':program_id'))
      ->setParameter('parent_program_ids', $parent_program_ids)
      ->setParameter('program_id', $program_id)
      ->getQuery()
      ->execute()
    ;
  }

  public function removeAllRelations(): void
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->delete()
      ->getQuery()
      ->execute()
    ;
  }

  public function markAllUnseenRelationsAsSeen(\DateTime $seen_at): void
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->update()
      ->set('b.seen_at', ':seen_at')
      ->setParameter(':seen_at', $seen_at)
      ->getQuery()
      ->execute()
    ;
  }

  public function remixCount(string $program_id): int
  {
    $qb = $this->createQueryBuilder('b');

    $result = $qb
      ->select('b')
      ->where($qb->expr()->eq('b.parent_id', ':program_id'))
      ->setParameter('program_id', $program_id)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }
}
