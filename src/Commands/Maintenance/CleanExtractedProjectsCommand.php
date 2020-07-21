<?php

namespace App\Commands\Maintenance;

use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CleanExtractedProjectsCommand.
 */
class CleanExtractedProjectsCommand extends Command
{
  protected static $defaultName = 'catrobat:clean:extracted';

  private ProgramManager $program_manager;

  private ExtractedFileRepository $extracted_file_repository;

  private EntityManagerInterface $entity_manager;

  private ?string $extract_path;

  public function __construct(ProgramManager $program_manager,
                              ExtractedFileRepository $extracted_file_repository,
                              EntityManagerInterface $entity_manager,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->extracted_file_repository = $extracted_file_repository;
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
    $this->extract_path = $parameter_bag->get('catrobat.file.extract.dir');
    if (!$this->extract_path)
    {
      throw new Exception('Invalid extract path given');
    }
  }

  protected function configure(): void
  {
    $this->setName('catrobat:clean:extracted')
      ->setDescription('Removes all extracted project data that is not used anymore.')
      ->addOption('remove-all', '', InputOption::VALUE_NONE,
        'All extracted data will be cleared. Project zips will be re-extracted when opening a project page.'
      )
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $remove_all = $input->getOption('remove-all');

    $projects = $this->program_manager->findAll();

    $directory_hashes_in_use = [];

    /** @var Program $project */
    foreach ($projects as $project)
    {
      if ($remove_all)
      {
        $project->setExtractedDirectoryHash(null);
        $this->entity_manager->persist($project);
      }
      elseif (null !== $project->getExtractedDirectoryHash())
      {
        array_push($directory_hashes_in_use, $project->getExtractedDirectoryHash());
      }
    }

    if ($remove_all)
    {
      $this->entity_manager->flush();
    }

    $extracted_project_dirs = glob($this->extract_path.'/*', GLOB_ONLYDIR);
    foreach ($extracted_project_dirs as $dir)
    {
      $split_dir_path = explode('/', $dir);

      $hash = end($split_dir_path);

      if ($remove_all || !in_array($hash, $directory_hashes_in_use, true))
      {
        try
        {
          Utils::removeDirectory($dir);
        }
        catch (Exception $e)
        {
          $output->writeln('Removing extracted project data failed with code '.$e->getCode());
          $output->writeln($e->getMessage());
          continue;
        }
      }
    }

    return 0;
  }
}
