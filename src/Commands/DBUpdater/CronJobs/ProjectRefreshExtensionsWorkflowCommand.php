<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Catrobat\Listeners\ProgramExtensionListener;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
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
  protected ProgramExtensionListener $program_extension_listener;
  protected ExtractedFileRepository $extracted_file_repo;

  public function __construct(ProgramManager $program_manager,
                              ProgramExtensionListener $program_extension_listener,
                              ExtractedFileRepository $extracted_file_repo)
  {
    parent::__construct();
    $this->program_manager = $program_manager;
    $this->program_extension_listener = $program_extension_listener;
    $this->extracted_file_repo = $extracted_file_repo;
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
    $projects = $this->program_manager->findAll();
    /** @var Program $project */
    foreach ($projects as $project) {
      $extracted_file = $this->extracted_file_repo->loadProgramExtractedFile($project);
      if (!is_null($extracted_file)) {
        $this->program_extension_listener->addExtensions($extracted_file, $project);
      }
    }
  }
}
