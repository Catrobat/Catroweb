<?php

namespace App\System\Commands\DBUpdater;

use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUserRankingCommand extends Command
{
  public function __construct(protected EntityManagerInterface $entity_manager, protected UserRepository $userRepository, protected ProgramRepository $programRepository)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->setName('catrobat:update:userranking')
      ->setDescription('Recomputes the ELO ranking for all users')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln('Recomputing ELO ranking for all users');

    $users = $this->userRepository->findAll();

    foreach ($users as $user) {
      $programs = $user->getPrograms();
      $programsCount = $programs->count();

      $downloadsCount = 0;
      foreach ($programs as $program) {
        $downloadsCount += $program->getDownloads();
      }

      if (0 != $downloadsCount && 0 !== $programsCount) {
        $elo = $downloadsCount / $programsCount;
        $user->setRankingScore(intval($elo));
        $this->entity_manager->persist($user);
        $this->entity_manager->flush();
      }
    }

    $output->writeln('Update finished!');

    return 0;
  }
}
