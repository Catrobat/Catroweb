<?php

namespace App\EventListener;

use App\Entity\User;
use App\Manager\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

class UserPostUpdateNotifier
{
  protected AchievementManager $achievement_manager;

  public function __construct(AchievementManager $achievement_manager)
  {
    $this->achievement_manager = $achievement_manager;
  }

  public function postUpdate(User $user, LifecycleEventArgs $event): void
  {
    $this->addPerfectProfileAchievement($user);
  }

  /**
   * @throws Exception
   */
  protected function addPerfectProfileAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementPerfectProfile($user);
  }
}
