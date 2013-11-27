<?php

namespace Catrobat\CatrowebBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;
use Catrobat\CatrowebBundle\Exceptions\InvalidStorageDirectoryException;

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
  
  public function extract($directory)
  {
    $full_dir_path = $this->compress_dir . $directory;
    $zip = new \ZipArchive;
    $filename = $directory . ".catrobat";
    
//     $zip = new \ZipArchive;
//     $res = $zip->open($file->getPathname());

//     if ($res === TRUE)
//     {
//       $zip->extractTo($full_extract_dir);
//       $zip->close();
//     }
//     else
//     {
//       throw new InvalidCatrobatFileException("unable to extract catrobat file");
//     }
    
//     return new ExtractedCatrobatFile($full_extract_dir);
  }
  
}