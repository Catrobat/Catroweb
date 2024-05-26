<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:workflow:achievement:silver_user', description: 'Unlocking silver_user user achievements')]
class AchievementWorkflow_SilverUser_Command extends Command
{
  public function __construct(protected UserManager $user_manager, protected AchievementManager $achievement_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->addSilverUserAchievementToEveryUser($output);

    return 0;
  }

  protected function addSilverUserAchievementToEveryUser(OutputInterface $output): void
  {
    $user_achievements = $this->achievement_manager->findUserAchievementsOfAchievement(Achievement::SILVER_USER);
    $excluded_user_id_list = array_map(static fn ($user_achievement) => $user_achievement->getUser()->getId(), $user_achievements);
    $active_user_ID_list = $this->user_manager->getActiveUserIDList(1);
    $user_id_list = array_values(array_diff($active_user_ID_list, $excluded_user_id_list));

    foreach ($user_id_list as $user_id) {
      /* @var User|null $user */
      $user = $this->user_manager->find($user_id);
      if (!is_null($user)) {
        try {
          $this->achievement_manager->unlockAchievementSilverUser($user);
        } catch (\Exception $exception) {
          $output->writeln($exception->getMessage());
        }
      }
    }
  }
}
