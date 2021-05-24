<?php

namespace App\Manager;

use App\Entity\Achievements\Achievement;
use App\Entity\Achievements\UserAchievement;
use App\Entity\User;
use App\Repository\Achievements\AchievementRepository;
use App\Repository\Achievements\UserAchievementRepository;
use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AchievementManager
{
  protected EntityManagerInterface $entity_manager;
  protected AchievementRepository $achievement_repository;
  protected UserAchievementRepository $user_achievement_repository;

  public function __construct(EntityManagerInterface $entity_manager,
                              AchievementRepository $achievement_repository,
                              UserAchievementRepository $user_achievement_repository)
  {
    $this->entity_manager = $entity_manager;
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
   * @throws Exception
   */
  public function unlockAchievement(User $user, string $internal_title, ?DateTime $unlocked_at = null): ?UserAchievement
  {
    $achievement = $this->findAchievementByInternalTitle($internal_title);
    if (is_null($achievement)) {
      return null;
    }

    $user_achievement = new UserAchievement();
    $user_achievement->setUser($user);
    $user_achievement->setAchievement($achievement);
    $user_achievement->setUnlockedAt($unlocked_at ?? TimeUtils::getDateTime());

    $this->entity_manager->persist($user_achievement);
    $this->entity_manager->flush();

    return $user_achievement;
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
