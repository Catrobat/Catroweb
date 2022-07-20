<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramDownloads;
use App\DB\Entity\User\User;
use App\Project\ProgramManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDownloadsCommand extends Command
{
  public function __construct(private readonly UserManager $user_manager, private readonly EntityManagerInterface $entity_manager,
    private readonly ProgramManager $program_manager)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:download')
      ->setDescription('download a project')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program which gets downloaded')
      ->addArgument('user_name', InputArgument::REQUIRED, 'User who download program')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_name = $input->getArgument('program_name');
    $user_name = $input->getArgument('user_name');

    $program = $this->program_manager->findOneByName($program_name);

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($user_name);

    if (null === $program || null === $user) {
      return 1;
    }

    try {
      $this->downloadProgram($program, $user);
    } catch (Exception) {
      return 2;
    }
    $output->writeln('Downloading '.$program->getName().' with user '.$user->getUsername());

    return 0;
  }

  private function downloadProgram(Program $program, User $user): void
  {
    $download = new ProgramDownloads();
    $download->setUser($user);
    $download->setProgram($program);
    $download->setDownloadedAt(date_create());
    $program->setDownloads($program->getDownloads() + 1);

    $this->entity_manager->persist($program);
    $this->entity_manager->persist($download);
    $this->entity_manager->flush();
  }
}
