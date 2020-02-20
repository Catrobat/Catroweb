<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\ConsoleProgressIndicator;
use App\Catrobat\Requests\AppRequest;
use App\Entity\Program;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReflavorExtensionCommand
 * @package App\Catrobat\Commands
 */
class ReflavorExtensionCommand extends Command
{
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var ProgramRepository
   */
  private $program_repository;

  /**
   * @var AppRequest
   */
  protected $app_request;

  /**
   * ReflavorExtensionCommand constructor.
   *
   * @param EntityManagerInterface $em
   * @param               $program_repo
   * @param AppRequest    $app_request
   */
  public function __construct(EntityManagerInterface $em, ProgramRepository $program_repo, AppRequest $app_request)
  {
    parent::__construct();
    $this->em = $em;
    $this->program_repository = $program_repo;
    $this->app_request = $app_request;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:reflavor:extension')
      ->setDescription('Reflavor programs with the given extension')
      ->addArgument('extension', InputArgument::REQUIRED, 'Extension')
      ->addArgument('flavor', InputArgument::REQUIRED, 'Flavor');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /**
     * @var $program Program
     */
    $extension = $input->getArgument('extension');
    $flavor = $input->getArgument('flavor');

    $offset = 0;
    $limit = 20;
    $programs = $this->program_repository->getProgramsByExtensionName(
      $extension, $this->app_request->isDebugBuildRequest(), $limit, $offset
    );
    $count = count($programs);

    $progress_indicator = new ConsoleProgressIndicator($output);

    for ($index = 1; $count !== 0; $index += 1)
    {
      foreach ($programs as $program)
      {
        $program->setFlavor($flavor);
        $this->em->persist($program);
        $progress_indicator->isSuccess();
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
  }
}
