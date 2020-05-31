<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProgramFileRepository
{
  public string $directory;

  protected string $web_path;

  private Filesystem $filesystem;

  private CatrobatFileCompressor $file_compressor;

  private string $tmp_dir;

  public function __construct(string $catrobat_file_storage_dir, string $catrobat_file_storage_path,
                              CatrobatFileCompressor $file_compressor, string $catrobat_upload_temp_dir)
  {
    $directory = $catrobat_file_storage_dir;
    $tmp_dir = $catrobat_upload_temp_dir;

    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory.' is not a valid directory');
    }

    if ($tmp_dir && !is_dir($tmp_dir))
    {
      throw new InvalidStorageDirectoryException($tmp_dir.' is not a valid directory');
    }

    $this->directory = $directory;
    $this->web_path = $catrobat_file_storage_path;
    $this->tmp_dir = $tmp_dir;
    $this->filesystem = new Filesystem();
    $this->file_compressor = $file_compressor;
  }

  public function saveProgram(ExtractedCatrobatFile $extracted, string $id): void
  {
    $this->file_compressor->compress($extracted->getPath(), $this->directory, $id);
  }

  public function saveProgramTemp(ExtractedCatrobatFile $extracted, string $id): void
  {
    if ('' !== $this->tmp_dir)
    {
      $this->file_compressor->compress($extracted->getPath(), $this->tmp_dir, $id);
    }
  }

  public function makeTempProgramPerm(string $id): void
  {
    if ('' !== $this->tmp_dir)
    {
      $this->filesystem->copy($this->tmp_dir.$id.'.catrobat', $this->directory.$id.'.catrobat', true);
      $this->filesystem->remove($this->tmp_dir.$id.'.catrobat');
    }
  }

  public function deleteProgramFile(string $id): void
  {
    $this->filesystem->remove($this->directory.$id.'.catrobat');
  }

  public function saveProgramFile(File $file, string $id): void
  {
    $this->filesystem->copy($file->getPathname(), $this->directory.$id.'.catrobat');
  }

  public function getProgramFile(string $id): File
  {
    return new File($this->directory.$id.'.catrobat');
  }
}
