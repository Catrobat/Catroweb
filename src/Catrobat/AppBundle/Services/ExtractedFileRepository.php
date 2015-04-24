<?php
namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\File\File;

class ExtractedFileRepository {
  private $local_path;
  private $webpath;
  private $local_storage_path;
  private $file_extractor;
  private $program_manager;
  private $prog_file_repo;

  function __construct($local_extracted_path, $web_extracted_path, $local_storage_path,
                       CatrobatFileExtractor $file_extractor, ProgramManager $program_manager, ProgramFileRepository $prog_file_rep)
  {
    if (!is_dir($local_extracted_path))
    {
      throw new InvalidStorageDirectoryException($local_extracted_path . " is not a valid directory");
    }
    $this->local_storage_path = $local_storage_path;
    $this->local_path = $local_extracted_path;
    $this->webpath = $web_extracted_path;
    $this->file_extractor = $file_extractor;
    $this->program_manager = $program_manager;
    $this->prog_file_repo = $prog_file_rep;
  }

  public function loadProgramExtractedFile(\Catrobat\AppBundle\Entity\Program $program)
  {
    try{
      $hash = $program->getExtractedDirectoryHash();
      $extracted_file = new ExtractedCatrobatFile($this->local_path.$hash."/",$this->webpath.$hash."/",$hash);
      return $extracted_file;
    }catch (InvalidCatrobatFileException $e)
    {
      //need to extract first
      $extracted_file = $this->file_extractor->extract($this->prog_file_repo->getProgramFile($program->getId()));
      $program->setExtractedDirectoryHash($extracted_file->getDirHash());
      $this->program_manager->save($program);
      return $extracted_file;
    }
  }

} 