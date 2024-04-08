<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:workflow:achievement:translation', description: 'Retroactively unlock bilingual, trilingual, linguist achievements')]
class AchievementWorkflow_Translation_Command extends Command
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly AchievementManager $achievement_manager)
  {
    parent::__construct();
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $users = $qb->select('u')
      ->from(ProjectCustomTranslation::class, 'e')
      ->from(Program::class, 'p')
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
