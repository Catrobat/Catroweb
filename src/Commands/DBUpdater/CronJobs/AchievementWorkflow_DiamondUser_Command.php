<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Entity\User;
use App\Entity\UserManager;
use App\Manager\AchievementManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AchievementWorkflow_DiamondUser_Command extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:workflow:achievement:diamond_user';

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
      ->setDescription('Unlocking diamond_user user achievements')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->addDiamondUserAchievementToEveryUser($output);

    return 0;
  }

  protected function addDiamondUserAchievementToEveryUser(OutputInterface $output): void
  {
    $active_user_ID_list = $this->user_manager->getActiveUserIDList(7);

    /* @var User|null $user */
    foreach ($active_user_ID_list as $user_id) {
      $user = $this->user_manager->find($user_id);
      if (!is_null($user)) {
        try {
          $this->achievement_manager->unlockAchievementSilverUser($user);
        } catch (Exception $exception) {
          $output->writeln($exception->getMessage());
        }
      }
    }
  }
}
