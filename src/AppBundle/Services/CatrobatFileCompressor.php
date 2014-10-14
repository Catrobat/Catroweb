<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Exceptions\InvalidCatrobatFileException;
use AppBundle\Model\ExtractedCatrobatFile;
use AppBundle\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\Finder\Finder;

class CatrobatFileCompressor
{
  public function __construct(){}

  public function compress($source, $destination, $archive_name)
  {
    if (!is_dir($source))
    {
      throw new InvalidStorageDirectoryException($source . " is not a valid target directory");
    }
    if (!is_dir($destination))
    {
      mkdir($destination, 0777, true);
    }

    $archive = new \ZipArchive;
    $filename = $archive_name . ".catrobat";

    $archive->open($destination. "/" . $filename, \ZipArchive::OVERWRITE);

    $finder = new Finder();
    $finder->in($source);

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