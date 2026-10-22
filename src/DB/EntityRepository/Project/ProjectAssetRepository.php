<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectAsset>
 */
class ProjectAssetRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, ProjectAsset::class);
  }

  /** @return array<string> */
  public function findOrphanedHashes(int $limit = 100): array
  {
    return array_map(strval(...), $this->createQueryBuilder('a')
      ->select('a.hash')
      ->where('NOT EXISTS (SELECT m.id FROM '.ProjectAssetMapping::class.' m WHERE m.asset = a)')
      ->setMaxResults($limit)
      ->getQuery()
      ->getSingleColumnResult());
  }
}
