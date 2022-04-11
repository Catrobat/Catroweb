<?php

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AchievementWorkflow_Translation_Command extends Command
{
  protected static $defaultName = 'catrobat:workflow:achievement:translation';

  private EntityManagerInterface $entity_manager;
  private AchievementManager $achievement_manager;

  public function __construct(EntityManagerInterface $entity_manager, AchievementManager $achievement_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->achievement_manager = $achievement_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Retroactively unlock bilingual, trilingual, linguist achievements')
    ;
  }

  /**
   * @throws Exception
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
