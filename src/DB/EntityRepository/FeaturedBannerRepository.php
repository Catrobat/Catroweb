<?php

declare(strict_types=1);

namespace App\DB\EntityRepository;

use App\DB\Entity\FeaturedBanner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeaturedBanner>
 */
class FeaturedBannerRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, FeaturedBanner::class);
  }

  /**
   * @return FeaturedBanner[]
   */
  public function findActiveBanners(int $limit = 10, int $offset = 0): array
  {
    $qb = $this->createQueryBuilder('fb');

    return $qb
      ->select('fb')
      ->where('fb.active = true')
      ->orderBy('fb.priority', 'DESC')
      ->addOrderBy('fb.created_on', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult()
    ;
  }
}
