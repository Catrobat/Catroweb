<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Storage\FileHelper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProjectFileRepository
{
  public string $zip_dir;

  protected string $extract_dir;

  private readonly Filesystem $filesystem;

  /**
   * @throws \Exception
   */
  public function __construct(
    #[Autowire('%catrobat.file.storage.dir%')]
    string $catrobat_file_storage_dir,
    #[Autowire('%catrobat.file.extract.dir%')]
    string $catrobat_file_extract_dir,
    private readonly CatrobatFileCompressor $file_compressor,
  ) {
    FileHelper::verifyDirectoryExists($catrobat_file_storage_dir);
    FileHelper::verifyDirectoryExists($catrobat_file_extract_dir);

    $this->zip_dir = $catrobat_file_storage_dir;
    $this->extract_dir = $catrobat_file_extract_dir;

    $this->filesystem = new Filesystem();
  }

  /**
   * @throws \Exception
   */
  public function zipProject(string $path, string $id): void
  {
    $this->file_compressor->compress($path, $this->zip_dir, $id);
  }

  /**
   * @throws \Exception
   */
  public function deleteProjectExtractFiles(string $id): void
  {
    FileHelper::removeDirectory($this->extract_dir.$id);
  }

  public function deleteProjectZipFileIfExists(string $id): void
  {
    if ($this->checkIfProjectZipFileExists($id)) {
      $this->deleteProjectZipFile($id);
    }
  }

  public function deleteProjectZipFile(string $id): void
  {
    unlink($this->zip_dir.$id.'.catrobat');
  }

  public function saveProjectZipFile(File $file, string $id): void
  {
    $this->filesystem->copy($file->getPathname(), $this->zip_dir.$id.'.catrobat');
  }

  public function getProjectZipFile(string $id): File
  {
    return new File($this->zip_dir.$id.'.catrobat');
  }

  public function checkIfProjectZipFileExists(string $id): bool
  {
    return file_exists($this->zip_dir.$id.'.catrobat');
  }
}
