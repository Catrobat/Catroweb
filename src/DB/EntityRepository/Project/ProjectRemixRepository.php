<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Remix\ProjectRemixRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectRemixRelation>
 */
class ProjectRemixRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectRemixRelation::class);
  }

  public function getAncestorRelations(array $descendant_program_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.descendant_id IN (:descendant_ids)')
      ->setParameter('descendant_ids', $descendant_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getAncestorIds(array $descendant_program_ids): array
  {
    $parents_catrobat_ancestor_relations = $this->getAncestorRelations($descendant_program_ids);

    return array_unique(array_map(static fn (ProjectRemixRelation $relation): string => $relation->getAncestorId(), $parents_catrobat_ancestor_relations));
  }

  public function getParentAncestorRelations(array $descendant_program_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.descendant_id IN (:descendant_ids)')
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('descendant_ids', $descendant_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getDirectAndIndirectDescendantRelations(
    array $ancestor_program_ids_to_exclude, array $descendant_program_ids,
  ): array {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.ancestor_id NOT IN (:ancestor_program_ids_to_exclude)')
      ->andWhere('r.descendant_id IN (:descendant_program_ids)')
      ->setParameter('ancestor_program_ids_to_exclude', $ancestor_program_ids_to_exclude)
      ->setParameter('descendant_program_ids', $descendant_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getDirectAndIndirectDescendantIds(
    array $ancestor_program_ids_to_exclude, array $descendant_program_ids,
  ): array {
    $direct_and_indirect_descendant_relations = $this
      ->getDirectAndIndirectDescendantRelations($ancestor_program_ids_to_exclude, $descendant_program_ids)
    ;

    return array_unique(array_map(static fn (ProjectRemixRelation $relation): string => $relation->getAncestorId(), $direct_and_indirect_descendant_relations));
  }

  public function getGraphDescendantIds(array $program_ids): array
  {
    $qb = $this->createQueryBuilder('r1');

    $result_data = $qb
      ->select('r2.descendant_id')
      ->innerJoin(Project::class, 'p', Join::ON, $qb->expr()->eq('r1.ancestor_id', 'p.id'))
      ->innerJoin(ProjectRemixRelation::class, 'r2', Join::ON, $qb->expr()->eq('r1.ancestor_id', 'r2.ancestor_id'))
      ->where('r1.descendant_id IN (:program_ids)')
      ->andWhere($qb->expr()->eq('p.remix_root', $qb->expr()->literal(true)))
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return array_unique(array_map(static fn (array $row): mixed => $row['descendant_id'], $result_data));
  }

  public function getDescendantRelations(array $ancestor_program_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.ancestor_id IN (:ancestor_program_ids)')
      ->setParameter('ancestor_program_ids', $ancestor_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getDescendantIds(array $ancestor_program_ids): array
  {
    $catrobat_root_descendant_relations = $this->getDescendantRelations($ancestor_program_ids);

    return array_unique(array_map(static fn (ProjectRemixRelation $relation): string => $relation->getDescendantId(), $catrobat_root_descendant_relations));
  }

  public function getDirectEdgeRelationsBetweenProgramIds(array $edge_start_program_ids, array $edge_end_program_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.ancestor_id IN (:edge_start_program_ids)')
      ->andWhere('r.descendant_id IN (:edge_end_program_ids)')
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('edge_start_program_ids', $edge_start_program_ids)
      ->setParameter('edge_end_program_ids', $edge_end_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function removeRelationsBetweenProgramIds(array $ancestor_program_ids, array $descendant_program_ids): void
  {
    $entities = $this->createQueryBuilder('r')
      ->where('r.ancestor_id IN (:ancestor_program_ids)')
      ->andWhere('r.descendant_id IN (:descendant_program_ids)')
      ->setParameter('ancestor_program_ids', $ancestor_program_ids)
      ->setParameter('descendant_program_ids', $descendant_program_ids)
      ->getQuery()
      ->getResult()
    ;

    foreach ($entities as $entity) {
      $this->getEntityManager()->remove($entity);
      $this->getEntityManager()->detach($entity);
    }

    $this->getEntityManager()->flush();
  }

  public function removeAllRelations(): void
  {
    $qb = $this->createQueryBuilder('r');

    $qb
      ->delete()
      ->getQuery()
      ->execute()
    ;
  }

  public function markAllUnseenRelationsAsSeen(\DateTime $seen_at): void
  {
    $qb = $this->createQueryBuilder('r');

    $qb
      ->update()
      ->set('r.seen_at', ':seen_at')
      ->setParameter(':seen_at', $seen_at)
      ->getQuery()
      ->execute()
    ;
  }

  public function remixCount(string $program_id): int
  {
    $qb = $this->createQueryBuilder('r');

    $result = $qb
      ->select('r')
      ->where($qb->expr()->eq('r.ancestor_id', ':program_id'))
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('program_id', $program_id)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }
}
