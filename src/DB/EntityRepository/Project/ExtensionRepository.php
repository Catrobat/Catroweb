<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Extension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Extension>
 */
class ExtensionRepository extends ServiceEntityRepository
{
  private const int CACHE_TTL = 86400;

  public function __construct(
    ManagerRegistry $managerRegistry,
    private readonly CacheInterface $cache,
  ) {
    parent::__construct($managerRegistry, Extension::class);
  }

  public function getExtensionByInternalTitle(string $internal_title): mixed
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where($qb->expr()->eq('e.internal_title', ':internal_title'))
      ->setParameter('internal_title', $internal_title)
      ->getQuery()
      ->getResult()
    ;
  }

  public function getActiveExtensions(): array
  {
    return $this->cache->get('active_extensions', function (ItemInterface $item): array {
      $item->expiresAfter(self::CACHE_TTL);

      return $this->createQueryBuilder('e')
        ->where('e.enabled = :enabled')
        ->setParameter('enabled', true)
        ->getQuery()
        ->getResult()
      ;
    });
  }
}
