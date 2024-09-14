<?php

declare(strict_types=1);

namespace App\User\EventListener;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\User\Achievements\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserPostUpdateNotifier
{
  public function __construct(protected AchievementManager $achievement_manager, protected VerifyEmail $verify_email)
  {
  }

  /**
   * @throws \Exception
   */
  public function postUpdate(User $user, LifecycleEventArgs $event): void
  {
    $this->achievement_manager->unlockAchievementPerfectProfile($user);
    $this->achievement_manager->unlockAchievementBronzeUser($user);

    $this->resetEmailVerificationOnEmailPropertyUpdate($user, $event);
  }

  protected function resetEmailVerificationOnEmailPropertyUpdate(User $user, LifecycleEventArgs $event): void
  {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = $event->getObjectManager();
    $unitOfWork = $entityManager->getUnitOfWork();
    $changes = $unitOfWork->getEntityChangeSet($user);

    if (isset($changes['email'])) {
      $user->setVerified(false);
      $entityManager->persist($user);
      $entityManager->flush();
      $this->verify_email->init($user)->send();
    }
  }
}
