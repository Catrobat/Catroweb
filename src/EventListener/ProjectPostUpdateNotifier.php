<?php

namespace App\EventListener;

use App\Entity\Program;
use App\Entity\User;
use App\Manager\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

class ProjectPostUpdateNotifier
{
  protected AchievementManager $achievement_manager;

  public function __construct(AchievementManager $achievement_manager)
  {
    $this->achievement_manager = $achievement_manager;
  }

  public function postUpdate(Program $project, LifecycleEventArgs $event): void
  {
    $user = $project->getUser();
    $this->addBronzeUserAchievement($user);
  }

  /**
   * @throws Exception
   */
  protected function addBronzeUserAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementBronzeUser($user);
  }
}
