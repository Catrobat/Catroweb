<?php

namespace App\System\Commands\DBUpdater;

use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
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

  public function __construct(protected EntityManagerInterface $entity_manager, protected AchievementManager $achievement_manager, protected UserManager $user_manager)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Adding/Updating/Deleting data in the Database')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    return 0;
  }
}
