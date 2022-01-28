<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Entity\Achievements\Achievement;
use App\Entity\User;
use App\Manager\AchievementManager;
use App\Manager\UserManager;
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
    $user_achievements = $this->achievement_manager->findUserAchievementsOfAchievement(Achievement::VERIFIED_DEVELOPER);
    $excluded_user_id_list = array_map(function ($user_achievement) { return $user_achievement->getUser()->getId(); }, $user_achievements);
    $user_ID_list = $this->user_manager->getUserIDList();
    $user_id_list = array_values(array_diff($user_ID_list, $excluded_user_id_list));

    foreach ($user_id_list as $user_id) {
      /* @var User|null $user */
      $user = $this->user_manager->find($user_id);
      if (!is_null($user)) {
        try {
          $this->achievement_manager->unlockAchievementVerifiedDeveloper($user);
        } catch (Exception $exception) {
          $output->writeln($exception->getMessage());
        }
      }
    }
  }
}
