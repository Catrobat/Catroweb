<?php

declare(strict_types=1);

namespace App\DB\EntityRepository;

use App\DB\Entity\Flavor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Flavor>
 */
class FlavorRepository extends ServiceEntityRepository
{
  private const int CACHE_TTL = 86400;

  public function __construct(
    ManagerRegistry $managerRegistry,
    private readonly CacheInterface $cache,
  ) {
    parent::__construct($managerRegistry, Flavor::class);
  }

  public function getFlavorByName(string $name): ?Flavor
  {
    return $this->findOneBy(['name' => $name]);
  }

  public function getFlavorsByNames(iterable $names): array
  {
    return $this->findBy(['name' => $names]);
  }

  public function getAllFlavors(): array
  {
    return $this->cache->get('all_flavors', function (ItemInterface $item): array {
      $item->expiresAfter(self::CACHE_TTL);

      return $this->findAll();
    });
  }
}
