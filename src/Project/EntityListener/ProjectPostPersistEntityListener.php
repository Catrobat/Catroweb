<?php

declare(strict_types=1);

namespace App\Project\EntityListener;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\System\StatisticRepository;
use App\User\Achievements\AchievementManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Project::class)]
class ProjectPostPersistEntityListener
{
  public function __construct(
    protected AchievementManager $achievement_manager,
    protected StatisticRepository $statistic_repository)
  {
  }

  /**
   * @throws \Exception
   */
  public function postPersist(Project $project, PostPersistEventArgs $args): void
  {
    $user = $project->getUser();
    $user->addProject($project);
    $this->achievement_manager->unlockAchievementBronzeUser($user);
    $this->statistic_repository->incrementProjects();
  }
}
