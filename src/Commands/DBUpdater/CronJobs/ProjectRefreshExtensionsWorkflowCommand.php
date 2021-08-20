<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Catrobat\Listeners\ProgramExtensionListener;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
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
  protected EntityManagerInterface $entity_manager;

  public function __construct(ProgramManager $program_manager,
                              ProgramExtensionListener $program_extension_listener,
                              ExtractedFileRepository $extracted_file_repo,
                              EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->program_manager = $program_manager;
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
    $iterator = $this->entity_manager->getRepository(Program::class)
      ->createQueryBuilder('e')
      ->getQuery()
      ->iterate();

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
