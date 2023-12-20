<?php

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\User\Achievements\Achievement;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUserRankingCommand extends Command
{

    /**
     * @param EntityManagerInterface $entity_manager
     * @param UserRepository $userRepository
     */
    public function __construct(protected EntityManagerInterface $entity_manager, protected UserRepository $userRepository, protected ProgramRepository $programRepository)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
  {
    $this
      ->setName('catrobat:update:userranking')
      ->setDescription('Recomputes the ELO ranking for all users')
    ;
  }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
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

            if($downloadsCount != 0 && $programsCount !== 0) {
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
