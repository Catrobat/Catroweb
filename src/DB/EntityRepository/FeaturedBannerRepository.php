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

  /**
   * Keyset cursor query for active banners ordered by priority DESC, id DESC.
   *
   * @return FeaturedBanner[]
   */
  public function findActiveBannersKeyset(int $limit, ?int $cursor_priority = null, ?string $cursor_id = null): array
  {
    $qb = $this->createQueryBuilder('fb')
      ->select('fb')
      ->where('fb.active = true')
      ->orderBy('fb.priority', 'DESC')
      ->addOrderBy('fb.id', 'DESC')
      ->setMaxResults($limit)
    ;

    if (null !== $cursor_priority && null !== $cursor_id) {
      $qb->andWhere(
        '(fb.priority < :cursor_priority) OR (fb.priority = :cursor_priority AND fb.id < :cursor_id)'
      )
        ->setParameter('cursor_priority', $cursor_priority)
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    return $qb->getQuery()->getResult();
  }
}
