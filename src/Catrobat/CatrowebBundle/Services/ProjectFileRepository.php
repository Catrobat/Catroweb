<?php
namespace Catrobat\CatrowebBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProjectFileRepository
{
  private $directory;
  private $filesystem;
  private $webpath;
  
  function __construct($directory, $webpath)
  {
    $this->directory = $directory;
    $this->webpath = $webpath;
    $this->filesystem = new Filesystem();
  }
  
  function saveProjectfile(File $file, $id)
  {
    $this->filesystem->copy($file->getPathname(), $this->directory . $id . ".catrobat");
  }
  
  function getProjectFile($id)
  {
    return new File($this->directory . $id . ".catrobat");
  }
  
  function getProjectFileWebUrl($id)
  {
    return $this->webpath . $id . ".catrobat";
  }
}
