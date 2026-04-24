<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Tag;
use App\User\Achievements\AchievementManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
  private const int CACHE_TTL = 86400;

  public function __construct(
    ManagerRegistry $managerRegistry,
    private readonly CacheInterface $cache,
  ) {
    parent::__construct($managerRegistry, Tag::class);
  }

  public function getActiveTags(): array
  {
    return $this->cache->get('active_tags', function (ItemInterface $item): array {
      $item->expiresAfter(self::CACHE_TTL);

      $tags = $this->createQueryBuilder('t')
        ->where('t.enabled = :enabled')
        ->setParameter('enabled', true)
        ->getQuery()
        ->getResult()
      ;

      $active_tags = [];
      /** @var Tag $tag */
      foreach ($tags as $tag) {
        if (Tag::CODING_JAM_09_2021 === $tag->getInternalTitle()) {
          if (AchievementManager::isCodingJam092021EventActive()) {
            $active_tags[] = $tag;
          }
        } else {
          $active_tags[] = $tag;
        }
      }

      return $active_tags;
    });
  }
}
