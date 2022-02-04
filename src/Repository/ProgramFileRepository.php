<?php

namespace App\Repository;

use App\Catrobat\Services\CatrobatFileCompressor;
use App\Utils\Utils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProgramFileRepository
{
  public string $zip_dir;

  protected string $extract_dir;

  private Filesystem $filesystem;

  private CatrobatFileCompressor $file_compressor;

  public function __construct(string $catrobat_file_storage_dir, string $catrobat_file_extract_dir,
                              CatrobatFileCompressor $file_compressor)
  {
    Utils::verifyDirectoryExists($catrobat_file_storage_dir);
    Utils::verifyDirectoryExists($catrobat_file_extract_dir);

    $this->zip_dir = $catrobat_file_storage_dir;
    $this->extract_dir = $catrobat_file_extract_dir;

    $this->filesystem = new Filesystem();
    $this->file_compressor = $file_compressor;
  }

  public function zipProject(string $path, string $id): void
  {
    $this->file_compressor->compress($path, $this->zip_dir, $id);
  }

  public function deleteProjectExtractFiles(string $id): void
  {
    Utils::removeDirectory($this->extract_dir.$id);
  }

  public function deleteProjectZipFileIfExists(string $id): void
  {
    if ($this->checkIfProjectZipFileExists($id)) {
      $this->deleteProjectZipFile($id);
    }
  }

  public function deleteProjectZipFile(string $id): void
  {
    $this->filesystem->remove($this->zip_dir.$id.'.catrobat');
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
    if (file_exists($this->zip_dir.$id.'.catrobat')) {
      return true;
    }

    return false;
  }
}
