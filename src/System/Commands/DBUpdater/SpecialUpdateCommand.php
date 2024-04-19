<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:special', description: 'Adding/Updating/Deleting data in the Database')]
class SpecialUpdateCommand extends Command
{
  public function __construct(protected EntityManagerInterface $entity_manager, protected AchievementManager $achievement_manager, protected UserManager $user_manager)
  {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    return 0;
  }
}
