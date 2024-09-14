<?php

declare(strict_types=1);

namespace App\User\EntityListener;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\User\Achievements\AchievementManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
class UserPostUpdateEntityListener
{
  public function __construct(protected AchievementManager $achievement_manager, protected VerifyEmail $verify_email)
  {
  }

  /**
   * @throws \Exception
   */
  public function postUpdate(User $user, PostUpdateEventArgs $args): void
  {
    $this->achievement_manager->unlockAchievementPerfectProfile($user);
    $this->achievement_manager->unlockAchievementBronzeUser($user);

    $this->resetEmailVerificationOnEmailPropertyUpdate($user, $args->getObjectManager());
  }

  protected function resetEmailVerificationOnEmailPropertyUpdate(User $user, EntityManagerInterface $entity_manager): void
  {
    $unit_of_work = $entity_manager->getUnitOfWork();
    $changes = $unit_of_work->getEntityChangeSet($user);

    if (isset($changes['email'])) {
      $user->setVerified(false);
      $entity_manager->persist($user);
      $entity_manager->flush();
      $this->verify_email->init($user)->send();
    }
  }
}
