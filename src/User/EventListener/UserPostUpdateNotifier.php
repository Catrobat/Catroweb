<?php

declare(strict_types=1);

namespace App\User\EventListener;

use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserPostUpdateNotifier
{
  public function __construct(protected AchievementManager $achievement_manager)
  {
  }

  /**
   * @throws TransportExceptionInterface
   */
  public function postUpdate(User $user, LifecycleEventArgs $event): void
  {
    $this->addPerfectProfileAchievement($user);
    $this->addBronzeUserAchievement($user);
  }

  /**
   * @throws \Exception
   */
  protected function addPerfectProfileAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementPerfectProfile($user);
  }

  /**
   * @throws \Exception
   */
  protected function addBronzeUserAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementBronzeUser($user);
  }
}
