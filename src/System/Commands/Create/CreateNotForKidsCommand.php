<?php

namespace App\System\Commands\Create;

use App\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNotForKidsCommand extends Command
{
  public function __construct(private readonly ProjectManager $remix_manipulation_program_manager, private readonly EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->setName('catrobat:notforkids')
      ->setDescription('mark a project as not safe for kids')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets marked')
      ->addArgument('type', InputArgument::REQUIRED, 'Type of marking (admin or user)')
    ;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $project_name = $input->getArgument('program_name');
    $type = $input->getArgument('type');

    $project = $this->remix_manipulation_program_manager->findOneByName($project_name);

    if (null === $project || null === $type) {
      $output->writeln('Marking '.$project_name.' as not safe for kids failed');

      return 1;
    }

    try {
      $project->setNotForKids($type);
      $this->entity_manager->persist($project);
      $this->entity_manager->flush();
    } catch (\Exception) {
      $output->writeln('Marking '.$project_name.' as not safe for kids failed');

      return 2;
    }

    $output->writeln('Marking '.$project_name.' as not safe for kids');

    return 0;
  }
}
