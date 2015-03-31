<?php

namespace Catrobat\AppBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use Catrobat\AppBundle\StatusCode;

class CatrobatFileExtractor
{
  private $extract_dir;
  private $extract_path;
  
  public function __construct($extract_dir, $extract_path)
  {
    if (!is_dir($extract_dir))
    {
      throw new InvalidStorageDirectoryException($extract_dir . " is not a valid directory");
    }
    $this->extract_dir = $extract_dir;
    $this->extract_path = $extract_path;
  }
  
  public function extract(File $file)
  {
    $temp_path = hash("md5",time() . mt_rand());
    $full_extract_dir = $this->extract_dir . $temp_path . "/";
    $full_extract_path = $this->extract_path . $temp_path . "/";
    
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
    
    return new ExtractedCatrobatFile($full_extract_dir,$full_extract_path);
  }
  
}