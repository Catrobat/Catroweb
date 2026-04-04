<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\ProjectAssetMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectAssetMapping>
 */
class ProjectAssetMappingRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, ProjectAssetMapping::class);
  }

  /** @return list<ProjectAssetMapping> */
  public function findByProjectId(string $projectId): array
  {
    return $this->createQueryBuilder('m')
      ->join('m.project', 'p')
      ->where('p.id = :projectId')
      ->setParameter('projectId', $projectId)
      ->getQuery()
      ->getResult()
    ;
  }

  public function deleteByProjectId(string $projectId): int
  {
    return $this->createQueryBuilder('m')
      ->delete()
      ->where('m.project = :projectId')
      ->setParameter('projectId', $projectId)
      ->getQuery()
      ->execute()
    ;
  }

  public function hasAnyForProject(string $projectId): bool
  {
    $count = $this->createQueryBuilder('m')
      ->select('COUNT(m.id)')
      ->join('m.project', 'p')
      ->where('p.id = :projectId')
      ->setParameter('projectId', $projectId)
      ->getQuery()
      ->getSingleScalarResult()
    ;

    return $count > 0;
  }
}
