<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Program;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\Extension\ProjectExtensionManager;
use App\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:workflow:project:refresh_extensions', description: 'Removes all extensions from a project an re-adds them again')]
class ProjectRefreshExtensionsWorkflowCommand extends Command
{
  public function __construct(protected ProjectManager $program_manager,
    protected ProgramRepository $program_repository,
    protected ProjectExtensionManager $extension_manager,
    protected ExtractedFileRepository $extracted_file_repo,
    protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
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
      $extracted_file = $this->extracted_file_repo->loadProjectExtractedFile($project);
      if (!is_null($extracted_file)) {
        $this->extension_manager->addExtensions($extracted_file, $project, false);
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
