<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Catrobat\Listeners\ProgramExtensionListener;
use App\Entity\Program;
use App\Manager\ProgramManager;
use App\Repository\ExtractedFileRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectRefreshExtensionsWorkflowCommand extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:workflow:project:refresh_extensions';

  protected ProgramManager $program_manager;
  protected ProgramRepository $program_repository;
  protected ProgramExtensionListener $program_extension_listener;
  protected ExtractedFileRepository $extracted_file_repo;
  protected EntityManagerInterface $entity_manager;

  public function __construct(ProgramManager $program_manager, ProgramRepository $program_repository,
                              ProgramExtensionListener $program_extension_listener,
                              ExtractedFileRepository $extracted_file_repo,
                              EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->program_manager = $program_manager;
    $this->program_repository = $program_repository;
    $this->program_extension_listener = $program_extension_listener;
    $this->extracted_file_repo = $extracted_file_repo;
    $this->entity_manager = $entity_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Removes all extensions from a project an re-adds them again')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->refreshProjectExtensions();

    return 0;
  }

  protected function refreshProjectExtensions(): void
  {
    $batchSize = 50;
    $i = 1;

    $iterator = $this->program_repository
      ->createQueryBuilder('e')
      ->getQuery()
      ->iterate()
    ;

    foreach ($iterator as $projects) {
      /** @var Program $project */
      $project = $projects[0];
      $extracted_file = $this->extracted_file_repo->loadProgramExtractedFile($project);
      if (!is_null($extracted_file)) {
        $this->program_extension_listener->addExtensions($extracted_file, $project, false);
      }
      ++$i;
      if (($i % $batchSize) === 0) {
        $this->entity_manager->flush();
        $this->entity_manager->clear();
      }
    }
    $this->entity_manager->flush();
    $this->entity_manager->clear();
  }
}
