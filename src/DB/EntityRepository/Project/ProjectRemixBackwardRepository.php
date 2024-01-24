<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Remix\ProjectRemixBackwardRelation;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRemixBackwardRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectRemixBackwardRelation::class);
  }

  /**
   * @return ProjectRemixBackwardRelation[]
   */
  public function getParentRelations(array $project_ids): array
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->where('b.child_id IN (:project_ids)')
      ->setParameter('project_ids', $project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @return ProjectRemixBackwardRelation[]
   */
  public function getDirectEdgeRelations(array $edge_start_project_ids, array $edge_end_project_ids): array
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->where('b.parent_id IN (:edge_start_project_ids)')
      ->andWhere('b.child_id IN (:edge_end_project_ids)')
      ->setParameter('edge_start_project_ids', $edge_start_project_ids)
      ->setParameter('edge_end_project_ids', $edge_end_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @param string[] $parent_project_ids
   */
  public function removeParentRelations(string $project_id, array $parent_project_ids): void
  {
    $qb = $this->createQueryBuilder('b');

    $qb
      ->delete()
      ->where('b.parent_id IN (:parent_project_ids)')
      ->andWhere($qb->expr()->eq('b.child_id', ':project_id'))
      ->setParameter('parent_project_ids', $parent_project_ids)
      ->setParameter('project_id', $project_id)
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

  /**
   * @return ProjectRemixBackwardRelation[]
   */
  public function getUnseenChildRelationsOfUser(User $user): array
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->select('b')
      ->innerJoin('b.parent', 'p', Join::WITH, 'b.parent_id = p.id')
      ->innerJoin('b.child', 'p2', Join::WITH, 'b.child_id = p2.id')
      ->where($qb->expr()->eq('p.user', ':user'))
      ->andWhere($qb->expr()->neq('p2.user', 'p.user'))
      ->andWhere($qb->expr()->isNull('b.seen_at'))
      ->orderBy('b.created_at', 'DESC')
      ->setParameter('user', $user)
      ->distinct()
      ->getQuery()
      ->getResult()
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

  public function remixCount(string $project_id): int
  {
    $qb = $this->createQueryBuilder('b');

    $result = $qb
      ->select('b')
      ->where($qb->expr()->eq('b.parent_id', ':project_id'))
      ->setParameter('project_id', $project_id)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }
}
