<?php

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AchievementWorkflow_Translation_Command extends Command
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly AchievementManager $achievement_manager)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:workflow:achievement:translation')
      ->setDescription('Retroactively unlock bilingual, trilingual, linguist achievements')
    ;
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $users = $qb->select('u')
      ->from(ProjectCustomTranslation::class, 'e')
      ->from(Project::class, 'p')
      ->from(User::class, 'u')
      ->where($qb->expr()->eq('e.project', 'p'))
      ->andWhere($qb->expr()->eq('p.user', 'u'))
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    foreach ($users as $user) {
      $this->achievement_manager->unlockAchievementCustomTranslation($user);
    }

    return 0;
  }
}
