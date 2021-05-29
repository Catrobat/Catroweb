<?php

namespace App\Commands\DBUpdater;

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
    $this->addPerfectProfileAchievementToEveryUser($output);
    $this->addBronzeUserAchievementToEveryUser($output);

    return 0;
  }

  /**
   * SHARE-487: Already registered users must also have the verified_developer badge.
   *            Can move to a workflow later.
   */
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

  /**
   * SHARE-487: Users that already changed their profile picture should have perfect_profile badge.
   *            Can move to a workflow later.
   */
  protected function addPerfectProfileAchievementToEveryUser(OutputInterface $output): void
  {
    $users = $this->user_manager->findAll();
    try {
      /** @var User $user */
      foreach ($users as $user) {
        $this->achievement_manager->unlockAchievementPerfectProfile($user);
      }
    } catch (Exception $e) {
      $output->writeln($e->getMessage());
    }
  }

  /**
   * SHARE-487: Users that already followed someone and have at least one upload should also get their badge
   *            Can move to a workflow later.
   */
  protected function addBronzeUserAchievementToEveryUser(OutputInterface $output): void
  {
    $users = $this->user_manager->findAll();
    try {
      /** @var User $user */
      foreach ($users as $user) {
        $this->achievement_manager->unlockAchievementBronzeUser($user);
      }
    } catch (Exception $e) {
      $output->writeln($e->getMessage());
    }
  }
}
