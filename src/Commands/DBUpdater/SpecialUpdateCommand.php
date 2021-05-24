<?php

namespace App\Commands\DBUpdater;

use App\Entity\Achievements\Achievement;
use App\Entity\User;
use App\Entity\UserManager;
use App\Manager\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpecialUpdateCommand extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:update:special';

  protected EntityManagerInterface $entity_manager;
  protected AchievementManager $achievement_manager;
  protected UserManager $user_manager;


  public function __construct(EntityManagerInterface $entity_manager, AchievementManager $achievement_manager, UserManager $user_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->achievement_manager = $achievement_manager;
    $this->user_manager = $user_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Adding/Updating/Deleting data in the Database')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->addVerifiedDeveloperAchievementToEveryUser($output);

    return 0;
  }


  /**
   * SHARE-487: Already registered users must also have the verified developer badge.
   *            Method can be removed after its execution on share.
   */
  protected function addVerifiedDeveloperAchievementToEveryUser(OutputInterface $output)
  {
    $users = $this->user_manager->findAll();
    try {
      /** @var User $user */
      foreach ($users as $user) {
        $this->achievement_manager->unlockAchievement($user, Achievement::VERIFIED_DEVELOPER, $user->getCreatedAt());
      }
    }
    catch (Exception $e) {
      $output->writeln($e->getMessage());
    }
  }
}
