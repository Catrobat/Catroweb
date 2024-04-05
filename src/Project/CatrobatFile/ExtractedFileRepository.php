<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\DB\Entity\Project\Program;
use App\Project\ProjectManager;
use App\Storage\FileHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExtractedFileRepository
{
  private readonly string $local_path;

  private readonly string $web_path;

  public function __construct(ParameterBagInterface $parameter_bag,
    private readonly ProjectManager $project_manager,
    private readonly LoggerInterface $logger)
  {
    $local_extracted_path = (string) $parameter_bag->get('catrobat.file.extract.dir');
    $web_extracted_path = (string) $parameter_bag->get('catrobat.file.extract.path');
    $local_storage_path = (string) $parameter_bag->get('catrobat.file.storage.dir');

    FileHelper::verifyDirectoryExists($local_extracted_path);
    FileHelper::verifyDirectoryExists($local_storage_path);

    $this->local_path = $local_extracted_path;
    $this->web_path = $web_extracted_path;
  }

  public function getBaseDir(string $id): string
  {
    return $this->local_path.$id.'/';
  }

  public function loadProjectExtractedFile(Program $project): ?ExtractedCatrobatFile
  {
    try {
      $project_id = $project->getId();

      return new ExtractedCatrobatFile($this->getBaseDir($project_id), $this->web_path.$project_id.'/', $project_id);
    } catch (InvalidCatrobatFileException) {
      return null;
    }
  }

  public function removeProjectExtractedFile(Program $project): void
  {
    try {
      $project_id = $project->getId();

      if (null === $project_id || !is_dir($this->local_path.$project_id.'/')) {
        return; // nothing to do
      }

      $extract_dir = $this->local_path.$project_id.'/';
      FileHelper::removeDirectory($extract_dir);
      $this->project_manager->save($project);
    } catch (\Exception $e) {
      $this->logger->error(
        "Removing extracted project files failed with code '".$e->getCode().
        "' and message: '".$e->getMessage()."'"
      );
    }
  }

  /**
   * @throws \Exception
   */
  public function saveProjectExtractedFile(ExtractedCatrobatFile $extracted_file): void
  {
    $file_overwritten = $extracted_file->getProjectXmlProperties()->asXML($extracted_file->getPath().'code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }
  }
}
