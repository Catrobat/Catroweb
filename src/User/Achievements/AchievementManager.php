<?php

namespace App\User\Achievements;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Achievements\UserAchievement;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\User\Achievements\AchievementRepository;
use App\DB\EntityRepository\User\Achievements\UserAchievementRepository;
use App\Project\ProjectManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;

class AchievementManager
{
  public function __construct(protected EntityManagerInterface $entity_manager, protected AchievementRepository $achievement_repository, protected UserAchievementRepository $user_achievement_repository, private readonly ProjectManager $project_manager, private readonly ProjectCustomTranslationRepository $project_custom_translation_repository)
  {
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

  public function findUserAchievementsOfAchievement(string $internal_title): array
  {
    $achievement = $this->findAchievementByInternalTitle($internal_title);

    return $this->user_achievement_repository->findBy([
      'achievement' => $achievement?->getId(),
    ]);
  }

  public function countUserAchievementsOfAchievement(int $achievement_id): int
  {
    return $this->user_achievement_repository->count([
      'achievement' => $achievement_id,
    ]);
  }

  public function isAchievementAlreadyUnlocked(string $user_id, int $achievement_id): bool
  {
    return $this->user_achievement_repository->count([
      'user' => $user_id,
      'achievement' => $achievement_id,
    ]) > 0;
  }

  /**
   * @return UserAchievement[]
   */
  public function findUserAchievements(User $user): array
  {
    return $this->user_achievement_repository->findUserAchievements($user);
  }

  public function countUnseenUserAchievements(User $user): int
  {
    return $this->user_achievement_repository->countUnseenUserAchievements($user);
  }

  public function readAllUnseenAchievements(User $user): void
  {
    $this->user_achievement_repository->readAllUnseenAchievements($user);
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
    $achievements_unlocked_id_list = array_map(fn (Achievement $achievement) => $achievement->getId(), $unlocked_achievements);

    return array_filter($achievements, fn (Achievement $achievement) => !in_array($achievement->getId(), $achievements_unlocked_id_list, true));
  }

  public function findMostRecentUserAchievement(User $user): ?UserAchievement
  {
    return $this->user_achievement_repository->findMostRecentUserAchievement($user);
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementVerifiedDeveloper(User $user): ?UserAchievement
  {
    return $this->unlockAchievement($user, Achievement::VERIFIED_DEVELOPER, $user->getCreatedAt());
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementCodingJam092021(User $user): ?UserAchievement
  {
    if (self::isCodingJam092021EventActive()) {
      return $this->unlockAchievement($user, Achievement::CODING_JAM_09_2021, $user->getCreatedAt());
    }

    return null;
  }

  public static function isCodingJam092021EventActive(): bool
  {
    // is open from 00:00 UTC+12 of 25th September 2021 till 23:59 UTC-12 of 26th September 2021.
    return TimeUtils::getTimestamp() >= 1_632_484_800 && TimeUtils::getTimestamp() <= 1_632_744_000;
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementPerfectProfile(User $user): ?UserAchievement
  {
    if (is_null($user->getAvatar())) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::PERFECT_PROFILE);
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementBronzeUser(User $user): ?UserAchievement
  {
    if (count($user->getFollowing()) <= 0) {
      return null;
    }

    if (count($user->getProjects()) <= 0) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::BRONZE_USER);
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementSilverUser(User $user): ?UserAchievement
  {
    if ($user->getCreatedAt() > new \DateTime('-1 years')) {
      return null;
    }
    $years_with_project_uploads = [];
    foreach ($user->getProjects() as $project) {
      /** @var Project $project */
      $year = $project->getUploadedAt()->format('Y');
      $years_with_project_uploads[$year] = true;
    }
    if (count($years_with_project_uploads) < 1) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::SILVER_USER);
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementGoldUser(User $user): ?UserAchievement
  {
    if ($user->getCreatedAt() > new \DateTime('-4 years')) {
      return null;
    }
    $years_with_project_uploads = [];
    foreach ($user->getProjects() as $project) {
      /** @var Project $project */
      $year = $project->getUploadedAt()->format('Y');
      $years_with_project_uploads[$year] = true;
    }
    if (count($years_with_project_uploads) < 4) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::GOLD_USER);
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementDiamondUser(User $user): ?UserAchievement
  {
    if ($user->getCreatedAt() > new \DateTime('-7 years')) {
      return null;
    }

    $years_with_project_uploads = [];
    foreach ($user->getProjects() as $project) {
      /** @var Project $project */
      $year = $project->getUploadedAt()->format('Y');
      $years_with_project_uploads[$year] = true;
    }
    if (count($years_with_project_uploads) < 7) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::DIAMOND_USER);
  }

  /**
   * @throws \Exception
   */
  public function unlockAchievementCustomTranslation(User $user): void
  {
    $projects = $this->project_manager->getPublicUserProjects($user->getId());

    $definedLanguages = $this->project_custom_translation_repository->countDefinedLanguages($projects);
    if ($definedLanguages >= 2) {
      $this->unlockAchievement($user, Achievement::BILINGUAL);
    }

    if ($definedLanguages >= 3) {
      $this->unlockAchievement($user, Achievement::TRILINGUAL);
    }

    if ($definedLanguages >= 5) {
      $this->unlockAchievement($user, Achievement::LINGUIST);
    }
  }

  /**
   * @throws \Exception
   */
  protected function unlockAchievement(User $user, string $internal_title, \DateTimeInterface $unlocked_at = null): ?UserAchievement
  {
    $achievement = $this->findAchievementByInternalTitle($internal_title);
    if (is_null($achievement)) {
      return null;
    }

    if ($this->isAchievementAlreadyUnlocked($user->getId(), $achievement->getId())) {
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
    return array_map(fn (UserAchievement $achievement) => $achievement->getAchievement(), $user_achievements);
  }
}
