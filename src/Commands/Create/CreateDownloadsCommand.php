<?php

namespace App\Commands\Create;

use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDownloadsCommand extends Command
{
  protected static $defaultName = 'catrobat:download';

  private UserManager $user_manager;

  private ProgramManager $program_manager;

  private EntityManagerInterface $entity_manager;

  public function __construct(UserManager $user_manager, EntityManagerInterface $entity_manager,
                              ProgramManager $program_manager)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
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

    if (null === $program || null === $user)
    {
      return 1;
    }

    try
    {
      $this->downloadProgram($program, $user);
    }
    catch (Exception $e)
    {
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
    $download->setIp('127.0.0.1');
    $download->setUserAgent('TestBrowser/5.0');
    $download->setLocale('de_at');
    $program->setDownloads($program->getDownloads() + 1);

    $this->entity_manager->persist($program);
    $this->entity_manager->persist($download);
    $this->entity_manager->flush();
  }
}
