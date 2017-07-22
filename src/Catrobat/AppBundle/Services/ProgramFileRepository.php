<?php

namespace Catrobat\AppBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;

class ProgramFileRepository
{
  private $directory;
  private $filesystem;
  private $webpath;
  private $file_compressor;

  public function __construct($directory, $webpath, CatrobatFileCompressor $file_compressor, $tmp_dir)
  {
    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory . ' is not a valid directory');
    }
    if ($tmp_dir && !is_dir($tmp_dir))
    {
      throw new InvalidStorageDirectoryException($tmp_dir . ' is not a valid directory');
    }

    $this->directory = $directory;
    $this->webpath = $webpath;
    $this->tmp_dir = $tmp_dir;
    $this->filesystem = new Filesystem();
    $this->file_compressor = $file_compressor;
  }

  public function saveProgram(ExtractedCatrobatFile $extracted, $id)
  {
    $this->file_compressor->compress($extracted->getPath(), $this->directory, $id);
  }

  public function saveProgramTemp(ExtractedCatrobatFile $extracted, $id)
  {
    if ($this->tmp_dir) {
      $this->file_compressor->compress($extracted->getPath(), $this->tmp_dir, $id);
    }
  }

  public function makeTempProgramPerm($id)
  {
    if ($this->tmp_dir) {
      $this->filesystem->copy($this->tmp_dir . $id . ".catrobat", $this->directory . $id . ".catrobat", true);
      $this->filesystem->remove($this->tmp_dir . $id . ".catrobat");
    }
  }

  public function saveProgramfile(File $file, $id)
  {
    $this->filesystem->copy($file->getPathname(), $this->directory . $id . '.catrobat');
  }

  public function getProgramFile($id)
  {
    return new File($this->directory . $id . '.catrobat');
  }
}
