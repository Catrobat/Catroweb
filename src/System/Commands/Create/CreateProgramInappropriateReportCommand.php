<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectInappropriateReport;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProgramInappropriateReportCommand extends Command
{
  public function __construct(private readonly UserManager $user_manager,
    private readonly ProjectManager $program_manager,
    private readonly EntityManagerInterface $entity_manager)
  {
    parent::__construct();
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
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = $input->getArgument('user');
    $program_name = $input->getArgument('program_name');
    $note = $input->getArgument('note');

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);
    $program = $this->program_manager->findOneByName($program_name);

    if (null === $user || null === $program) {
      return 1;
    }

    if ($program->getUser() === $user) {
      return 2;
    }

    try {
      $this->reportProgram($program, $user, $note);
    } catch (\Exception) {
      return 3;
    }
    $output->writeln('Reporting '.$program->getName());
    $output->writeln('ReportedUser = '.$program->getUser());

    return 0;
  }

  private function reportProgram(Project $program, User $user, string $note): void
  {
    $report = new ProjectInappropriateReport();
    $report->setReportingUser($user);
    $program->setVisible(false);
    $report->setCategory('Inappropriate');
    $report->setNote($note);
    $report->setProject($program);
    $report->setReportedUser($program->getUser());
    $this->entity_manager->persist($report);
    $this->entity_manager->flush();
  }
}
