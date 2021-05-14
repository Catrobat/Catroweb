<?php

namespace App\Manager;

use App\Entity\Achievements\Achievement;
use App\Entity\Achievements\UserAchievement;
use App\Entity\User;
use App\Repository\Achievements\AchievementRepository;
use App\Repository\Achievements\UserAchievementRepository;

class AchievementManager
{
  protected AchievementRepository $achievement_repository;

  protected UserAchievementRepository $user_achievement_repository;

  public function __construct(AchievementRepository $achievement_repository, UserAchievementRepository $user_achievement_repository)
  {
    $this->achievement_repository = $achievement_repository;
    $this->user_achievement_repository = $user_achievement_repository;
  }

  public function findAchievementByInternalTitle(string $internal_title): ?Achievement
  {
    return $this->achievement_repository->findAchievementByInternalTitle($internal_title);
  }

  /**
   * @return Achievement[]
   */
  public function findAllEnabledAchievements(): array
  {
    return $this->achievement_repository->findAllEnabledAchievements();
  }

  /**
   * @return Achievement[]
   */
  public function findAllAchievements(): array
  {
    return $this->achievement_repository->findAll();
  }

  public function countAllEnabledAchievements(): int
  {
    return $this->achievement_repository->countAllEnabledAchievements();
  }

  /**
   * @return UserAchievement[]
   */
  public function findAllUserAchievements(): array
  {
    return $this->user_achievement_repository->findAll();
  }

  /**
   * @return UserAchievement[]
   */
  public function findUserAchievements(User $user): array
  {
    return $this->user_achievement_repository->findUserAchievements($user);
  }

  /**
   * @return Achievement[]
   */
  public function findUnlockedAchievements(User $user): array
  {
    return $this->mapUserAchievementsToAchievements($this->findUserAchievements($user));
  }

  /**
   * @return Achievement[]
   */
  public function findLockedAchievements(User $user): array
  {
    $achievements = $this->findAllEnabledAchievements();
    $unlocked_achievements = $this->findUnlockedAchievements($user);
    $achievements_unlocked_id_list = array_map(function (Achievement $achievement) {
      return $achievement->getId();
    }, $unlocked_achievements);

    return array_filter($achievements, function (Achievement $achievement) use ($achievements_unlocked_id_list) {
      return !in_array($achievement->getId(), $achievements_unlocked_id_list, true);
    });
  }

  public function findMostRecentUserAchievement(User $user): ?UserAchievement
  {
    return $this->user_achievement_repository->findMostRecentUserAchievement($user);
  }

  /**
   * @param UserAchievement[] $user_achievements
   *
   * @return Achievement[]
   */
  protected function mapUserAchievementsToAchievements(array $user_achievements): array
  {
    return array_map(function (UserAchievement $achievement) {
      return $achievement->getAchievement();
    }, $user_achievements);
  }
}
