<?php

declare(strict_types=1);

namespace App\Api\Services\Achievements;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Achievements\UserAchievement;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;

class AchievementsApiLoader extends AbstractApiLoader
{
  public function __construct(private readonly AchievementManager $achievement_manager)
  {
  }

  /**
   * @return array{unlocked: Achievement[], locked: Achievement[], most_recent: ?UserAchievement, total_count: int, unlocked_count: int}
   */
  public function getAchievementsPageData(User $user): array
  {
    $all_enabled = $this->achievement_manager->findAllEnabledAchievements();
    $user_achievements = $this->achievement_manager->findUserAchievements($user);

    $unlocked = array_map(static fn (UserAchievement $ua): Achievement => $ua->getAchievement(), $user_achievements);
    $unlocked_ids = array_map(static fn (Achievement $a): ?int => $a->getId(), $unlocked);
    $locked = array_values(array_filter($all_enabled, static fn (Achievement $a): bool => !in_array($a->getId(), $unlocked_ids, true)));

    $most_recent = $user_achievements[0] ?? null;

    return [
      'unlocked' => $unlocked,
      'locked' => $locked,
      'most_recent' => $most_recent,
      'total_count' => count($all_enabled),
      'unlocked_count' => count($unlocked),
    ];
  }

  public function getUnseenCount(User $user): int
  {
    return $this->achievement_manager->countUnseenUserAchievements($user);
  }
}
