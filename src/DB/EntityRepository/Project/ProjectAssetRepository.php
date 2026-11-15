<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

  /**
   * Orphans in hash order, after $after: the caller pages by key, so a batch never rescans what it already passed.
   *
   * @return array<string, int> hash => size in bytes
   */
  public function findOrphans(int $limit, \DateTimeImmutable $created_before, string $after = ''): array
  {
    $rows = $this->orphans($created_before)
      ->select('a.hash, a.size')
      ->andWhere('a.hash > :after')
      ->setParameter('after', $after)
      ->orderBy('a.hash')
      ->setMaxResults($limit)
      ->getQuery()
      ->getArrayResult()
    ;

    $orphans = [];
    foreach ($rows as $row) {
      $orphans[(string) $row['hash']] = (int) $row['size'];
    }

    return $orphans;
  }

  /** @return array{count: int, bytes: int} */
  public function countOrphans(\DateTimeImmutable $created_before): array
  {
    $row = $this->orphans($created_before)
      ->select('COUNT(a.hash) AS count, COALESCE(SUM(a.size), 0) AS bytes')
      ->getQuery()
      ->getSingleResult()
    ;

    return ['count' => (int) $row['count'], 'bytes' => (int) $row['bytes']];
  }

  private function orphans(\DateTimeImmutable $created_before): QueryBuilder
  {
    return $this->createQueryBuilder('a')
      ->where('NOT EXISTS (SELECT m.id FROM '.ProjectAssetMapping::class.' m WHERE m.asset = a)')
      ->andWhere('a.createdAt < :created_before')
      ->setParameter('created_before', $created_before)
    ;
  }
}
