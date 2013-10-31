<?php
namespace Catrobat\CatrowebBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProjectRepository
{
  private $directory = "";
  private $filesystem;
  
  
  function __construct($directory)
  {
    $this->directory = $directory;
    $this->filesystem = new Filesystem();
  }
  
  function saveProjectfile(File $file, $id)
  {
    $this->filesystem->copy($file->getPathname(), $this->directory . $id . ".catrobat");
  }
  
  function getProjectFile($handle)
  {
    
  }
  
  function getProjectFileWebUrl($handle)
  {
    
  }
}
