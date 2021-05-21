<?php

namespace App\Repository\Achievements;

use App\Entity\Achievements\UserAchievement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserAchievementRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserAchievement::class);
  }

  /**
   * @return UserAchievement[]
   */
  public function findUserAchievements(User $user): array
  {
    return $this->findBy(['user' => $user], ['unlocked_at' => 'DESC']);
  }

  public function countUserAchievements(User $user): int
  {
    return $this->count(['user' => $user]);
  }

  public function findMostRecentUserAchievement(User $user): ?UserAchievement
  {
    return $this->findOneBy(['user' => $user], ['unlocked_at' => 'DESC']);
  }
}
