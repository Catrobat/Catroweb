<?php

namespace Catrobat\CoreBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Catrobat\CoreBundle\Exceptions\InvalidStorageDirectoryException;
use Catrobat\CoreBundle\StatusCode;

class CatrobatFileExtractor
{
  private $extract_dir;
  
  public function __construct($extract_dir)
  {
    $filesystem = new Filesystem();
    if (!is_dir($extract_dir))
    {
      throw new InvalidStorageDirectoryException($extract_dir . " is not a valid directory");
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
      throw new InvalidCatrobatFileException("Error extracting catrobat file",StatusCode::INVALID_FILE);
    }
    
    return new ExtractedCatrobatFile($full_extract_dir);
  }
  
}