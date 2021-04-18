<?php

namespace App\Commands;

use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReflavorExtensionCommand extends Command
{
  protected static $defaultName = 'catrobat:reflavor:extension';

  private EntityManagerInterface $em;

  private ProgramManager $program_manager;

  public function __construct(EntityManagerInterface $em, ProgramManager $program_manager)
  {
    parent::__construct();
    $this->em = $em;
    $this->program_manager = $program_manager;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:reflavor:extension')
      ->setDescription('Reflavor programs with the given extension')
      ->addArgument('extension', InputArgument::REQUIRED, 'Extension')
      ->addArgument('flavor', InputArgument::REQUIRED, 'Flavor')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $extension = $input->getArgument('extension');
    $flavor = $input->getArgument('flavor');

    $offset = 0;
    $limit = 20;
    $programs = $this->program_manager->getProjectsByExtensionName($extension, $limit, $offset);
    $count = count($programs);

    for ($index = 1; 0 !== $count; ++$index) {
      foreach ($programs as $program) {
        $program->setFlavor($flavor);
        $this->em->persist($program);
      }

      $this->em->flush();

      $offset = $index * $limit;
      $programs = $this->program_manager->getProjectsByExtensionName($extension, $limit, $offset);
      $count = count($programs);
    }

    $output->writeln('');
    $output->writeln('done.');

    return 0;
  }
}
