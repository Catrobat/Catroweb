<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Achievements;

use App\DB\Entity\User\Achievements\UserAchievement;
use App\DB\Entity\User\User;
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

  public function countUnseenUserAchievements(User $user): int
  {
    return $this->count(['user' => $user, 'seen_at' => null]);
  }

  public function findMostRecentUserAchievement(User $user): ?UserAchievement
  {
    return $this->findOneBy(['user' => $user], ['unlocked_at' => 'DESC']);
  }

  public function readAllUnseenAchievements(User $user): void
  {
    $user_achievements = $this->findUserAchievements($user);
    foreach ($user_achievements as $user_achievement) {
      if (is_null($user_achievement->getSeenAt())) {
        $user_achievement->setSeenAt(new \DateTime('now'));
        $this->getEntityManager()->persist($user_achievement);
      }
    }

    $this->getEntityManager()->flush();
  }
}
