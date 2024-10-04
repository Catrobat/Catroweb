<?php

declare(strict_types=1);

namespace App\User\EntityListener;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\System\StatisticRepository;
use App\Security\Authentication\VerifyEmail;
use App\User\Achievements\AchievementManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
class UserPostPersistEntityListener
{
  public function __construct(
    protected AchievementManager $achievement_manager,
    protected VerifyEmail $verify_email,
    protected StatisticRepository $statistic_repository)
  {
  }

  /**
   * @throws \Exception
   */
  public function postPersist(User $user, PostPersistEventArgs $args): void
  {
    $this->achievement_manager->unlockAchievementAccountCreated($user);
    if (!$user->isVerified()) {
      $this->verify_email->init($user)->send();
    }
    $this->statistic_repository->incrementUser();
  }
}
