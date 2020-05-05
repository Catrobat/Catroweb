<?php

namespace App\Commands;

use App\Catrobat\Requests\AppRequest;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReflavorExtensionCommand extends Command
{
  protected static $defaultName = 'catrobat:reflavor:extension';

  protected AppRequest $app_request;

  private EntityManagerInterface $em;

  private ProgramRepository $program_repository;

  public function __construct(EntityManagerInterface $em, ProgramRepository $program_repo, AppRequest $app_request)
  {
    parent::__construct();
    $this->em = $em;
    $this->program_repository = $program_repo;
    $this->app_request = $app_request;
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
    $programs = $this->program_repository->getProgramsByExtensionName(
      $extension, $this->app_request->isDebugBuildRequest(), $limit, $offset
    );
    $count = count($programs);

    for ($index = 1; 0 !== $count; ++$index)
    {
      foreach ($programs as $program)
      {
        $program->setFlavor($flavor);
        $this->em->persist($program);
      }

      $this->em->flush();

      $offset = $index * $limit;
      $programs = $this->program_repository->getProgramsByExtensionName(
        $extension, $this->app_request->isDebugBuildRequest(), $limit, $offset
      );
      $count = count($programs);
    }

    $output->writeln('');
    $output->writeln('done.');

    return 0;
  }
}
