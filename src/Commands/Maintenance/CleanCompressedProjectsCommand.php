<?php

namespace App\Commands\Maintenance;

use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CleanCompressedProjectsCommand.
 */
class CleanCompressedProjectsCommand extends Command
{
  protected static $defaultName = 'catrobat:clean:compressed';

  private ProgramManager $program_manager;

  private ExtractedFileRepository $extracted_file_repository;

  private EntityManagerInterface $entity_manager;

  private ?string $compressed_path;

  public function __construct(ProgramManager $program_manager,
                              ExtractedFileRepository $extracted_file_repository,
                              EntityManagerInterface $entity_manager,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->extracted_file_repository = $extracted_file_repository;
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
    $this->compressed_path = $parameter_bag->get('catrobat.file.storage.dir');
    if (!$this->compressed_path)
    {
      throw new Exception('Invalid extract path given');
    }
  }

  protected function configure(): void
  {
    $this->setName('catrobat:clean:compressed')
      ->setDescription('Removes all compressed project data.')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $files = glob($this->compressed_path.'*'); // get all file names
    foreach ($files as $file)
    {
      if (is_file($file))
      {
        unlink($file);
      }
    }

    return 0;
  }
}
