<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Scratch\ScratchProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScratchProject>
 */
class ScratchProjectRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProject::class);
  }

  /**
   * @param string[] $scratch_program_ids
   */
  public function getProgramDataByIds(array $scratch_program_ids): array
  {
    $qb = $this->createQueryBuilder('s');

    return $qb
      ->select(['s.id', 's.name', 's.username'])
      ->where('s.id IN (:scratch_program_ids)')
      ->setParameter('scratch_program_ids', $scratch_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * Searches the Scratch projects we know by name and Scratch username. Projects already imported as a
   * Catrobat project are left out: they show up as projects (or are hidden there for a reason).
   *
   * @return ScratchProject[]
   */
  public function search(string $query, int $limit, int $offset): array
  {
    return $this->createSearchQueryBuilder($query)
      ->orderBy('s.name', 'ASC')
      ->addOrderBy('s.id', 'ASC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult()
    ;
  }

  public function searchCount(string $query): int
  {
    return (int) $this->createSearchQueryBuilder($query)
      ->select('COUNT(s.id)')
      ->getQuery()
      ->getSingleScalarResult()
    ;
  }

  private function createSearchQueryBuilder(string $query): QueryBuilder
  {
    // ponytail: LIKE '%…%' scans the table; move Scratch projects into Elasticsearch if it grows large.
    return $this->createQueryBuilder('s')
      ->where('s.name LIKE :query OR s.username LIKE :query')
      ->andWhere('NOT EXISTS (SELECT p.id FROM '.Project::class.' p WHERE p.scratch_id = s.id)')
      ->setParameter('query', '%'.addcslashes(trim($query), '%_\\').'%')
    ;
  }
}
