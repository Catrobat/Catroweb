<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class ProgramRemixRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProgramRemixRelation::class);
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

    return array_unique(array_map(fn (ProgramRemixRelation $relation): string => $relation->getAncestorId(), $parents_catrobat_ancestor_relations));
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
    array $ancestor_program_ids_to_exclude, array $descendant_program_ids
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
    array $ancestor_program_ids_to_exclude, array $descendant_program_ids
  ): array {
    $direct_and_indirect_descendant_relations = $this
      ->getDirectAndIndirectDescendantRelations($ancestor_program_ids_to_exclude, $descendant_program_ids)
    ;

    return array_unique(array_map(fn (ProgramRemixRelation $relation): string => $relation->getAncestorId(), $direct_and_indirect_descendant_relations));
  }

  public function getRootProgramIds(array $program_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    $result_data = $qb
      ->select('r.ancestor_id')
      ->innerJoin(Program::class, 'p', Join::WITH, $qb->expr()->eq('r.ancestor_id', 'p.id')->__toString())
      ->where('r.descendant_id IN (:program_ids)')
      ->andWhere($qb->expr()->eq('p.remix_root', $qb->expr()->literal(true)))
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return array_unique(array_map(fn ($row): mixed => $row['ancestor_id'], $result_data));
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

    return array_unique(array_map(fn (ProgramRemixRelation $relation): string => $relation->getDescendantId(), $catrobat_root_descendant_relations));
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
    $qb = $this->createQueryBuilder('r');

    $qb
      ->delete()
      ->where('r.ancestor_id IN (:ancestor_program_ids)')
      ->andWhere('r.descendant_id IN (:descendant_program_ids)')
      ->setParameter('ancestor_program_ids', $ancestor_program_ids)
      ->setParameter('descendant_program_ids', $descendant_program_ids)
      ->getQuery()
      ->execute()
    ;
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

  /**
   * @return ProgramRemixRelation[]
   */
  public function getUnseenDirectDescendantRelationsOfUser(User $user): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->innerJoin('r.ancestor', 'p', Join::WITH, 'r.ancestor_id = p.id')
      ->innerJoin('r.descendant', 'p2', Join::WITH, 'r.descendant_id = p2.id')
      ->where($qb->expr()->eq('p.user', ':user'))
      ->andWhere($qb->expr()->neq('p2.user', 'p.user'))
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->andWhere($qb->expr()->isNull('r.seen_at'))
      ->orderBy('r.created_at', 'DESC')
      ->setParameter('user', $user)
      ->distinct()
      ->getQuery()
      ->getResult()
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

  /**
   * @return ProgramRemixRelation[]
   */
  public function getDirectParentRelationDataOfUser(string $user_id): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r.ancestor_id, r.descendant_id')
      ->innerJoin('r.descendant', 'p', Join::WITH, 'r.descendant_id = p.id')
      ->where($qb->expr()->eq('IDENTITY(p.user)', ':user_id'))
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('user_id', $user_id)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @return ProgramRemixRelation[]
   */
  public function getDirectParentRelationsOfUsersRemixes(array $user_ids, string $exclude_user_id, array $exclude_program_ids, string $flavor): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->innerJoin('r.ancestor', 'pa', Join::WITH, 'r.ancestor_id = pa.id')
      ->innerJoin('r.descendant', 'pd', Join::WITH, 'r.descendant_id = pd.id')
      ->where($qb->expr()->in('IDENTITY(pd.user)', ':user_ids'))
      ->andWhere($qb->expr()->neq('IDENTITY(pa.user)', ':exclude_user_id'))
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->andWhere($qb->expr()->notIn('r.ancestor_id', ':exclude_program_ids'))
      ->andWhere($qb->expr()->eq('pa.visible', $qb->expr()->literal(true)))
      ->andWhere($qb->expr()->eq('pa.flavor', ':flavor'))
      ->andWhere($qb->expr()->eq('pa.private', $qb->expr()->literal(false)))
      ->setParameter('user_ids', $user_ids)
      ->setParameter('exclude_user_id', $exclude_user_id)
      ->setParameter('exclude_program_ids', $exclude_program_ids)
      ->setParameter('flavor', $flavor)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }
}
