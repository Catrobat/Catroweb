<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\FeaturedStudio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeaturedStudio>
 */
class FeaturedStudioRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, FeaturedStudio::class);
  }

  /**
   * @return FeaturedStudio[]
   */
  public function findActiveFeatured(int $limit = 20, int $offset = 0): array
  {
    $qb = $this->createQueryBuilder('fs');

    return $qb
      ->select('fs')
      ->addSelect('s')
      ->leftJoin('fs.studio', 's')
      ->where('fs.active = true')
      ->orderBy('fs.priority', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult()
    ;
  }

  public function countActiveFeatured(): int
  {
    $qb = $this->createQueryBuilder('fs');

    return (int) $qb
      ->select('COUNT(fs.id)')
      ->where('fs.active = true')
      ->getQuery()
      ->getSingleScalarResult()
    ;
  }
}
