<?php

namespace App\EventListener;

use App\Entity\User;
use App\Manager\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

class UserPostPersistNotifier
{
  protected AchievementManager $achievement_manager;

  public function __construct(AchievementManager $achievement_manager)
  {
    $this->achievement_manager = $achievement_manager;
  }

  public function postPersist(User $user, LifecycleEventArgs $event): void
  {
    $this->addVerifiedDeveloperAchievement($user);
  }

  /**
   * @throws Exception
   */
  protected function addVerifiedDeveloperAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementVerifiedDeveloper($user);
  }
}
