<?php
namespace AppBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Exceptions\InvalidStorageDirectoryException;

class ProgramFileRepository
{
  private $directory;
  private $filesystem;
  private $webpath;
  
  function __construct($directory, $webpath)
  {
    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory . " is not a valid directory");
    }
    $this->directory = $directory;
    $this->webpath = $webpath;
    $this->filesystem = new Filesystem();
  }
  
  function saveProgramfile(File $file, $id)
  {
    $this->filesystem->copy($file->getPathname(), $this->directory . $id . ".catrobat");
  }
  
  function getProgramFile($id)
  {
    return new File($this->directory . $id . ".catrobat");
  }
  
}
