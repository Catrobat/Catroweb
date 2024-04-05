<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Achievements;

use App\DB\Entity\User\Achievements\Achievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AchievementRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
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
    return $this->findBy(['enabled' => true], ['priority' => 'ASC']);
  }

  public function countAllEnabledAchievements(): int
  {
    return $this->count(['enabled' => true]);
  }
}
