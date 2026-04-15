<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\DB\Entity\Project\Project;
use App\Project\ProjectManager;
use App\Storage\FileHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ExtractedFileRepository
{
  private readonly string $local_path;

  private readonly string $web_path;

  private readonly string $storage_path;

  /**
   * @throws \Exception
   */
  public function __construct(ParameterBagInterface $parameter_bag,
    private readonly ProjectManager $project_manager,
    private readonly CatrobatFileExtractor $file_extractor,
    private readonly LoggerInterface $logger)
  {
    /** @var string $local_extracted_path */
    $local_extracted_path = $parameter_bag->get('catrobat.file.extract.dir');
    /** @var string $web_extracted_path */
    $web_extracted_path = $parameter_bag->get('catrobat.file.extract.path');
    /** @var string $local_storage_path */
    $local_storage_path = $parameter_bag->get('catrobat.file.storage.dir');

    FileHelper::verifyDirectoryExists($local_extracted_path);
    FileHelper::verifyDirectoryExists($local_storage_path);

    $this->local_path = $local_extracted_path;
    $this->web_path = $web_extracted_path;
    $this->storage_path = $local_storage_path;
  }

  public function getBaseDir(string $id): string
  {
    return $this->local_path.$id.'/';
  }

  public function loadProjectExtractedFile(Project $project): ?ExtractedCatrobatFile
  {
    try {
      $project_id = $project->getId();
      if (null === $project_id) {
        return null;
      }

      $base_dir = $this->getBaseDir($project_id);

      if (!is_dir($base_dir)) {
        $this->reExtractProject($project_id);
      }

      return new ExtractedCatrobatFile($base_dir, $this->web_path.$project_id.'/', $project_id);
    } catch (InvalidCatrobatFileException) {
      return null;
    }
  }

  public function removeProjectExtractedFile(Project $project): void
  {
    try {
      $project_id = $project->getId();

      if (null === $project_id || !is_dir($this->local_path.$project_id.'/')) {
        return; // nothing to do
      }

      $extract_dir = $this->local_path.$project_id.'/';
      FileHelper::removeDirectory($extract_dir);
      $this->project_manager->save($project);
    } catch (\Exception $exception) {
      $this->logger->error(
        "Removing extracted project files failed with code '".$exception->getCode().
        "' and message: '".$exception->getMessage()."'"
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

  /**
   * Re-extracts a project from its .catrobat zip file into the extract directory.
   */
  private function reExtractProject(string $project_id): void
  {
    $zip_path = $this->storage_path.$project_id.'.catrobat';

    if (!file_exists($zip_path)) {
      $this->logger->warning('Cannot re-extract project '.$project_id.': zip file not found at '.$zip_path);

      return;
    }

    try {
      $extracted = $this->file_extractor->extract(new File($zip_path));

      $target_dir = $this->local_path.$project_id;
      $filesystem = new Filesystem();

      if (is_dir($target_dir)) {
        $filesystem->remove($target_dir);
      }

      $filesystem->rename($extracted->getPath(), $target_dir);
    } catch (\Exception $e) {
      $this->logger->error('Failed to re-extract project '.$project_id.': '.$e->getMessage());
    }
  }
}
