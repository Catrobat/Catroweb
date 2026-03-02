<?php

declare(strict_types=1);

namespace App\Api\Services\Achievements;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Achievements\UserAchievement;
use OpenAPI\Server\Model\AchievementResponse;
use OpenAPI\Server\Model\AchievementsCountResponse;
use OpenAPI\Server\Model\AchievementsListResponse;

class AchievementsResponseManager extends AbstractResponseManager
{
  /**
   * @param Achievement[] $unlocked
   * @param Achievement[] $locked
   */
  public function createAchievementsListResponse(
    array $unlocked,
    array $locked,
    ?UserAchievement $most_recent_user_achievement,
    int $total_count,
    int $unlocked_count,
    ?string $locale = null,
  ): AchievementsListResponse {
    $unlocked_responses = array_map(fn (Achievement $a) => $this->createAchievementResponse($a, $locale), $unlocked);
    $locked_responses = array_map(fn (Achievement $a) => $this->createAchievementResponse($a, $locale), $locked);

    $most_recent_response = null;
    $most_recent_unlocked_at = null;
    $show_animation = false;

    if (null !== $most_recent_user_achievement) {
      $most_recent_achievement = $most_recent_user_achievement->getAchievement();
      $most_recent_id = $most_recent_achievement->getId();
      foreach ($unlocked_responses as $r) {
        if ($r->getId() === $most_recent_id) {
          $most_recent_response = $r;
          break;
        }
      }
      $most_recent_response ??= $this->createAchievementResponse($most_recent_achievement, $locale);
      $most_recent_unlocked_at = $most_recent_user_achievement->getUnlockedAt()?->format('Y-m-d');
      $show_animation = null === $most_recent_user_achievement->getSeenAt();
    }

    return new AchievementsListResponse([
      'unlocked' => array_values($unlocked_responses),
      'locked' => array_values($locked_responses),
      'most_recent' => $most_recent_response,
      'most_recent_unlocked_at' => $most_recent_unlocked_at,
      'show_animation' => $show_animation,
      'total_count' => $total_count,
      'unlocked_count' => $unlocked_count,
    ]);
  }

  /**
   * @param Achievement[] $achievements
   *
   * @return AchievementResponse[]
   */
  public function createAchievementResponseList(array $achievements, ?string $locale = null): array
  {
    return array_values(array_map(fn (Achievement $a) => $this->createAchievementResponse($a, $locale), $achievements));
  }

  public function createAchievementsCountResponse(int $count): AchievementsCountResponse
  {
    return new AchievementsCountResponse([
      'count' => $count,
    ]);
  }

  private function createAchievementResponse(Achievement $achievement, ?string $locale = null): AchievementResponse
  {
    return new AchievementResponse([
      'id' => $achievement->getId(),
      'internal_title' => $achievement->getInternalTitle(),
      'title' => $this->trans($achievement->getTitleLtmCode(), [], $locale),
      'description' => $this->trans($achievement->getDescriptionLtmCode(), [], $locale),
      'badge_svg_path' => '/'.$achievement->getBadgeSvgPath(),
      'badge_locked_svg_path' => '/'.$achievement->getBadgeLockedSvgPath(),
      'banner_svg_path' => '/'.$achievement->getBannerSvgPath(),
      'banner_color' => $achievement->getBannerColor(),
    ]);
  }
}
