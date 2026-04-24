<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Achievements;

use App\DB\Entity\User\Achievements\Achievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Achievement>
 */
class AchievementRepository extends ServiceEntityRepository
{
  private const int CACHE_TTL = 86400;

  public function __construct(
    ManagerRegistry $managerRegistry,
    private readonly CacheInterface $cache,
  ) {
    parent::__construct($managerRegistry, Achievement::class);
  }

  public function findAchievementByInternalTitle(string $internal_title): ?Achievement
  {
    return $this->findOneBy(['internal_title' => $internal_title], ['priority' => 'ASC']);
  }

  /**
   * @return Achievement[]
   */
  public function findAllEnabledAchievements(): array
  {
    return $this->cache->get('enabled_achievements', function (ItemInterface $item): array {
      $item->expiresAfter(self::CACHE_TTL);

      return $this->findBy(['enabled' => true], ['priority' => 'ASC']);
    });
  }

  public function countAllEnabledAchievements(): int
  {
    return $this->count(['enabled' => true]);
  }
}
