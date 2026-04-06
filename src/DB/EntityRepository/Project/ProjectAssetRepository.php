<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\ProjectAsset;
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

  public function findByHash(string $hash): ?ProjectAsset
  {
    return $this->find($hash);
  }

  /** @return list<ProjectAsset> */
  public function findOrphanedAssets(int $limit = 100): array
  {
    return $this->createQueryBuilder('a')
      ->where('a.referenceCount <= 0')
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult()
    ;
  }
}
