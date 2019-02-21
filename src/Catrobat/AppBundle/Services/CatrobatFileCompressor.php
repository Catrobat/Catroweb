<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\Finder\Finder;

/**
 * Class CatrobatFileCompressor
 * @package Catrobat\AppBundle\Services
 */
class CatrobatFileCompressor
{
  /**
   * CatrobatFileCompressor constructor.
   */
  public function __construct()
  {
  }

  /**
   * @param $source
   * @param $destination
   * @param $archive_name
   *
   * @return string
   */
  public function compress($source, $destination, $archive_name)
  {
    if (!is_dir($source))
    {
      throw new InvalidStorageDirectoryException($source . ' is not a valid target directory');
    }
    if (!is_dir($destination))
    {
      mkdir($destination, 0777, true);
    }

    $archive = new \ZipArchive();
    $filename = $archive_name . '.catrobat';

    $archive->open($destination . '/' . $filename, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);

    $finder = new Finder();
    $finder->in($source);

    foreach ($finder as $element)
    {
      if ($element->isDir())
      {
        $archive->addEmptyDir($element->getRelativePathname() . '/');
      }
      elseif ($element->isFile())
      {
        $archive->addFile($element->getRealpath(), $element->getRelativePathname());
      }
    }
    $archive->close();

    return $destination . '/' . $filename;
  }
}
