<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\System\Statistic;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\System\StatisticRepository;
use App\DB\EntityRepository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:special', description: 'Adding/Updating/Deleting data in the Database')]
class SpecialUpdateCommand extends Command
{
  public function __construct(
    protected EntityManagerInterface $entity_manager,
    protected StatisticRepository $statistic_repository,
    protected UserRepository $user_repository,
    protected ProgramRepository $project_repository,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $statistic = $this->statistic_repository->find(1);
    if ($statistic?->getUsers()) {
      return 0;
    }

    $statistic = new Statistic();
    $statistic->setProjects((string) $this->project_repository->count());
    $statistic->setUsers((string) $this->user_repository->count());
    $this->entity_manager->persist($statistic);
    $this->entity_manager->flush();

    return 0;
  }
}
