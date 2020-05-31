<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

class ExtractedFileRepository
{
  private string $local_path;

  private string $web_path;

  private string $local_storage_path;

  private CatrobatFileExtractor $file_extractor;

  private ProgramManager $program_manager;

  private ProgramFileRepository $program_file_repo;

  private LoggerInterface $l;

  public function __construct(ParameterBagInterface $parameter_bag, CatrobatFileExtractor $file_extractor,
                              ProgramManager $program_manager, ProgramFileRepository $program_file_repo,
                              LoggerInterface $l)
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
    $this->l = $l;
  }

  public function loadProgramExtractedFile(Program $program): ?ExtractedCatrobatFile
  {
    try
    {
      $hash = $program->getExtractedDirectoryHash();

      return new ExtractedCatrobatFile($this->local_path.$hash.'/', $this->web_path.$hash.'/', $hash);
    }
    catch (InvalidCatrobatFileException $e)
    {
      //need to extract first
      unset($e);
    }

    try
    {
      $program_file = $this->program_file_repo->getProgramFile($program->getId());
      $extracted_file = $this->file_extractor->extract($program_file);
      $program->setExtractedDirectoryHash($extracted_file->getDirHash());
      $this->program_manager->save($program);

      return $extracted_file;
    }
    catch (Exception $e)
    {
      return null;
    }
  }

  public function removeProgramExtractedFile(Program $program): void
  {
    try
    {
      $hash = $program->getExtractedDirectoryHash();

      if (null != $hash)
      {
        $path = $this->local_path.$hash.'/';

        if (file_exists($this->local_path.$hash) && is_dir($path))
        {
          $finder = new Finder();

          $image_path = $path.'images/';
          if (file_exists($path.'images') && is_dir($image_path))
          {
            $finder->files()->in($image_path);
            foreach ($finder as $file)
            {
              unlink($image_path.$file->getFilename());
            }
            rmdir($image_path);
          }

          $finder = new Finder();

          $sound_path = $path.'sounds/';
          if (file_exists($path.'sounds') && is_dir($sound_path))
          {
            $finder->files()->in($sound_path);
            foreach ($finder as $file)
            {
              unlink($sound_path.$file->getFilename());
            }
            rmdir($sound_path);
          }
          /** @var Finder $finder */
          $finder = new Finder();
          $finder->files()->in($path);
          foreach ($finder as $file)
          {
            unlink($path.$file->getFilename());
          }

          rmdir($path);
        }

        $program->setExtractedDirectoryHash(null);
        $this->program_manager->save($program);
      }
    }
    catch (InvalidCatrobatFileException $e)
    {
      // do nothing
    }
  }
}
