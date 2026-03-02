<?php

declare(strict_types=1);

namespace App\Api\Services\Achievements;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;

class AchievementsApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly AchievementManager $achievement_manager)
  {
  }

  public function markAllAsSeen(User $user): void
  {
    $this->achievement_manager->readAllUnseenAchievements($user);
  }
}
