<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Entity\User;
use App\Entity\UserManager;
use App\Manager\AchievementManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AchievementWorkflow_VerifiedDeveloper_Command extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:workflow:achievement:verified_developer';

  protected UserManager $user_manager;
  protected AchievementManager $achievement_manager;

  public function __construct(UserManager $user_manager, AchievementManager $achievement_manager)
  {
    parent::__construct();
    $this->achievement_manager = $achievement_manager;
    $this->user_manager = $user_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Unlocking verified_developer user achievements')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->addVerifiedDeveloperAchievementToEveryUser($output);

    return 0;
  }

  protected function addVerifiedDeveloperAchievementToEveryUser(OutputInterface $output): void
  {
    $users = $this->user_manager->findAll();
    try {
      /** @var User $user */
      foreach ($users as $user) {
        $this->achievement_manager->unlockAchievementVerifiedDeveloper($user);
      }
    } catch (Exception $e) {
      $output->writeln($e->getMessage());
    }
  }
}
