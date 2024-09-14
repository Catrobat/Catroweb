<?php

declare(strict_types=1);

namespace App\User\EventListener;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\User\Achievements\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserPostPersistNotifier
{
  public function __construct(
    protected AchievementManager $achievement_manager,
    protected VerifyEmail $verify_email)
  {
  }

  /**
   * @throws \Exception
   */
  public function postPersist(User $user, LifecycleEventArgs $event): void
  {
    $this->achievement_manager->unlockAchievementAccountCreated($user);
    if (!$user->isVerified()) {
      $this->verify_email->init($user)->send();
    }
  }
}
