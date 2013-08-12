<?php

namespace Catrobat\CatrowebBundle\Helper;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;

class CatrobatFileExtractor
{
  private $extract_dir;
  
  public function __construct($extract_dir)
  {
    $filesystem = new Filesystem();
    if ($filesystem->exists($extract_dir))
    {
      if (!is_dir($extract_dir))
      {
        throw new \Exception();
      }
    }
    else
    {
      $filesystem->mkdir($extract_dir);
    }
    $this->extract_dir = $extract_dir;
  }
  
  public function extract(File $file)
  {
    $temp_path = hash("md5",time() . mt_rand());
    $full_extract_dir = $this->extract_dir . $temp_path . "/";
    
    $zip = new \ZipArchive;
    $res = $zip->open($file->getPathname());

    if ($res === TRUE)
    {
      $zip->extractTo($full_extract_dir);
      $zip->close();
    }
    else
    {
      throw new InvalidCatrobatFileException("unable to extract catrobat file");
    }
    
    return $full_extract_dir;
  }
  
}