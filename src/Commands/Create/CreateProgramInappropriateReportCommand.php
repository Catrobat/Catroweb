<?php

namespace App\Commands\Create;

use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProgramInappropriateReportCommand extends Command
{
  protected static $defaultName = 'catrobat:report';

  private UserManager $user_manager;

  private ProgramManager $program_manager;

  private EntityManagerInterface $entity_manager;

  public function __construct(UserManager $user_manager,
                              ProgramManager $program_manager,
                              EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:report')
      ->setDescription('Report a project')
      ->addArgument('user', InputArgument::REQUIRED, 'User who reports on program')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets reported')
      ->addArgument('note', InputArgument::REQUIRED, 'Report message')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = $input->getArgument('user');
    $program_name = $input->getArgument('program_name');
    $note = $input->getArgument('note');

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);
    $program = $this->program_manager->findOneByName($program_name);

    if (null === $user || null === $program)
    {
      return 1;
    }

    if ($program->getUser() === $user)
    {
      return 2;
    }

    try
    {
      $this->reportProgram($program, $user, $note);
    }
    catch (Exception $e)
    {
      return 3;
    }
    $output->writeln('Reporting '.$program->getName());

    return 0;
  }

  private function reportProgram(Program $program, User $user, string $note): void
  {
    $report = new ProgramInappropriateReport();
    $report->setReportingUser($user);
    $program->setVisible(false);
    $report->setCategory('Inappropriate');
    $report->setNote($note);
    $report->setProgram($program);
    $this->entity_manager->persist($report);
    $this->entity_manager->flush();
  }
}
