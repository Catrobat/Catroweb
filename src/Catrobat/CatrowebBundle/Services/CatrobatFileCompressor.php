<?php

namespace Catrobat\CatrowebBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;
use Catrobat\CatrowebBundle\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\Finder\Finder;

class CatrobatFileCompressor
{
  private $compress_dir;
  
  public function __construct($compress_dir)
  {
    $filesystem = new Filesystem();
    if (!is_dir($compress_dir))
    {
      throw new InvalidStorageDirectoryException($compress_dir . " is not a valid directory");
    }
    $this->compress_dir = $compress_dir;
  }
  
  public function compress($directory)
  {
    $full_dir_path = $this->compress_dir . $directory . "/";
    if (!is_dir($full_dir_path))
    {
      throw new InvalidCatrobatFileException("invalid directory");
    }
    $archive = new \ZipArchive;
    $filename = $directory . ".catrobat";
    
    $archive->open($this->compress_dir . $filename, \ZipArchive::OVERWRITE);
    $finder = new Finder();      
    $finder->in($full_dir_path);
    foreach ($finder as $element)
    {        
      if ($element->isDir()) 
      {
        $archive->addEmptyDir($element->getRelativePathname() . "/");
      } 
      elseif ($element->isFile())
      {
        $archive->addFile($element->getRealpath(), $element->getRelativePathname());
      }
    }
    $archive->close();
  }  
}