<?php

namespace Catrobat\AppBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use Catrobat\AppBundle\StatusCode;
use Catrobat\AppBundle\Exceptions\Upload\InvalidArchiveException;

class CatrobatFileExtractor
{
  private $extract_dir;
  private $extract_path;

  public function __construct($extract_dir, $extract_path)
  {
    if (!is_dir($extract_dir))
    {
      throw new InvalidStorageDirectoryException($extract_dir . ' is not a valid directory');
    }
    $this->extract_dir = $extract_dir;
    $this->extract_path = $extract_path;
  }

  public function extract(File $file)
  {
    $temp_path = hash('md5', time() . mt_rand());
    $full_extract_dir = $this->extract_dir . $temp_path . '/';
    $full_extract_path = $this->extract_path . $temp_path . '/';

    $zip = new \ZipArchive();
    $res = $zip->open($file->getPathname());

    if ($res === true)
    {
      $zip->extractTo($full_extract_dir);
      $zip->close();
    }
    else
    {
      throw new InvalidArchiveException();
    }

    return new ExtractedCatrobatFile($full_extract_dir, $full_extract_path, $temp_path);
  }

  public function getExtractDir()
  {
    return $this->extract_dir;
  }

  public function getExtractPath()
  {
    return $this->extract_path;
  }
}
