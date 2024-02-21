<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Remix\ProjectRemixRelation;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRemixRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectRemixRelation::class);
  }

  public function getAncestorRelations(array $descendant_project_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.descendant_id IN (:descendant_ids)')
      ->setParameter('descendant_ids', $descendant_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getAncestorIds(array $descendant_project_ids): array
  {
    $parents_catrobat_ancestor_relations = $this->getAncestorRelations($descendant_project_ids);

    return array_unique(array_map(fn (ProjectRemixRelation $relation) => $relation->getAncestorId(), $parents_catrobat_ancestor_relations));
  }

  public function getParentAncestorRelations(array $descendant_project_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.descendant_id IN (:descendant_ids)')
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('descendant_ids', $descendant_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getDirectAndIndirectDescendantRelations(
    array $ancestor_project_ids_to_exclude, array $descendant_project_ids
  ): array {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.ancestor_id NOT IN (:ancestor_project_ids_to_exclude)')
      ->andWhere('r.descendant_id IN (:descendant_project_ids)')
      ->setParameter('ancestor_project_ids_to_exclude', $ancestor_project_ids_to_exclude)
      ->setParameter('descendant_project_ids', $descendant_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getDirectAndIndirectDescendantIds(
    array $ancestor_project_ids_to_exclude, array $descendant_project_ids
  ): array {
    $direct_and_indirect_descendant_relations = $this
      ->getDirectAndIndirectDescendantRelations($ancestor_project_ids_to_exclude, $descendant_project_ids)
    ;

    return array_unique(array_map(fn (ProjectRemixRelation $relation) => $relation->getAncestorId(), $direct_and_indirect_descendant_relations));
  }

  public function getRootProjectIds(array $project_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    $result_data = $qb
      ->select('r.ancestor_id')
      ->innerJoin(Project::class, 'p', Join::WITH, $qb->expr()->eq('r.ancestor_id', 'p.id')->__toString())
      ->where('r.descendant_id IN (:project_ids)')
      ->andWhere($qb->expr()->eq('p.remix_root', $qb->expr()->literal(true)))
      ->setParameter('project_ids', $project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return array_unique(array_map(fn ($row) => $row['ancestor_id'], $result_data));
  }

  public function getDescendantRelations(array $ancestor_project_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.ancestor_id IN (:ancestor_project_ids)')
      ->setParameter('ancestor_project_ids', $ancestor_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function getDescendantIds(array $ancestor_project_ids): array
  {
    $catrobat_root_descendant_relations = $this->getDescendantRelations($ancestor_project_ids);

    return array_unique(array_map(fn (ProjectRemixRelation $relation) => $relation->getDescendantId(), $catrobat_root_descendant_relations));
  }

  public function getDirectEdgeRelationsBetweenProjectIds(array $edge_start_project_ids, array $edge_end_project_ids): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->where('r.ancestor_id IN (:edge_start_project_ids)')
      ->andWhere('r.descendant_id IN (:edge_end_project_ids)')
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('edge_start_project_ids', $edge_start_project_ids)
      ->setParameter('edge_end_project_ids', $edge_end_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function removeRelationsBetweenProjectIds(array $ancestor_project_ids, array $descendant_project_ids): void
  {
    $qb = $this->createQueryBuilder('r');

    $qb
      ->delete()
      ->where('r.ancestor_id IN (:ancestor_project_ids)')
      ->andWhere('r.descendant_id IN (:descendant_project_ids)')
      ->setParameter('ancestor_project_ids', $ancestor_project_ids)
      ->setParameter('descendant_project_ids', $descendant_project_ids)
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
   * @return ProjectRemixRelation[]
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

  public function remixCount(string $project_id): int
  {
    $qb = $this->createQueryBuilder('r');

    $result = $qb
      ->select('r')
      ->where($qb->expr()->eq('r.ancestor_id', ':project_id'))
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->setParameter('project_id', $project_id)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }

  /**
   * @return ProjectRemixRelation[]
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
   * @return ProjectRemixRelation[]
   */
  public function getDirectParentRelationsOfUsersRemixes(array $user_ids, string $exclude_user_id, array $exclude_project_ids, string $flavor): array
  {
    $qb = $this->createQueryBuilder('r');

    return $qb
      ->select('r')
      ->innerJoin('r.ancestor', 'pa', Join::WITH, 'r.ancestor_id = pa.id')
      ->innerJoin('r.descendant', 'pd', Join::WITH, 'r.descendant_id = pd.id')
      ->where($qb->expr()->in('IDENTITY(pd.user)', ':user_ids'))
      ->andWhere($qb->expr()->neq('IDENTITY(pa.user)', ':exclude_user_id'))
      ->andWhere($qb->expr()->eq('r.depth', $qb->expr()->literal(1)))
      ->andWhere($qb->expr()->notIn('r.ancestor_id', ':exclude_project_ids'))
      ->andWhere($qb->expr()->eq('pa.visible', $qb->expr()->literal(true)))
      ->andWhere($qb->expr()->eq('pa.flavor', ':flavor'))
      ->andWhere($qb->expr()->eq('pa.private', $qb->expr()->literal(false)))
      ->setParameter('user_ids', $user_ids)
      ->setParameter('exclude_user_id', $exclude_user_id)
      ->setParameter('exclude_project_ids', $exclude_project_ids)
      ->setParameter('flavor', $flavor)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }
}
