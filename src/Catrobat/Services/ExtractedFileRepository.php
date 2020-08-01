<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Utils\Utils;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExtractedFileRepository
{
  private string $local_path;

  private string $web_path;

  private string $local_storage_path;

  private CatrobatFileExtractor $file_extractor;

  private ProgramManager $program_manager;

  private ProgramFileRepository $program_file_repo;

  private LoggerInterface $logger;

  public function __construct(ParameterBagInterface $parameter_bag, CatrobatFileExtractor $file_extractor,
                              ProgramManager $program_manager, ProgramFileRepository $program_file_repo,
                              LoggerInterface $logger)
  {
    $local_extracted_path = $parameter_bag->get('catrobat.file.extract.dir');
    $web_extracted_path = $parameter_bag->get('catrobat.file.extract.path');
    $local_storage_path = $parameter_bag->get('catrobat.file.storage.dir');

    if (!is_dir($local_extracted_path))
    {
      throw new InvalidStorageDirectoryException($local_extracted_path.' is not a valid directory');
    }
    $this->local_storage_path = $local_storage_path;
    $this->local_path = $local_extracted_path;
    $this->web_path = $web_extracted_path;
    $this->file_extractor = $file_extractor;
    $this->program_manager = $program_manager;
    $this->program_file_repo = $program_file_repo;
    $this->logger = $logger;
  }

  public function loadProgramExtractedFile(Program $program): ?ExtractedCatrobatFile
  {
    try
    {
      $program_id = $program->getId();

      return new ExtractedCatrobatFile($this->local_path.$program_id.'/', $this->web_path.$program_id.'/', $program_id);
    }
    catch (InvalidCatrobatFileException $e)
    {
      return null;
    }
  }

  public function removeProgramExtractedFile(Program $program): void
  {
    try
    {
      $program_id = $program->getId();

      if (null === $program_id || !is_dir($this->local_path.$program_id.'/'))
      {
        return; // nothing to do
      }

      $extract_dir = $this->local_path.$program_id.'/';
      Utils::removeDirectory($extract_dir);
      $this->program_manager->save($program);
    }
    catch (Exception $e)
    {
      $this->logger->error(
        "Removing extracted project files failed with code '".$e->getCode().
        "' and message: '".$e->getMessage()."'"
      );
    }
  }
}
