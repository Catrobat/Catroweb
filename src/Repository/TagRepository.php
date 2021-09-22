<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Manager\AchievementManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Tag::class);
  }

  public function getActiveTags(): array
  {
    $tags = $this->findBy([
      'enabled' => true,
    ]);

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
  }
}
