<?php

namespace App\Catrobat\Services;

use App\Utils\Utils;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class CatrobatFileCompressor
{
  /**
   * @param mixed $source
   * @param mixed $destination
   * @param mixed $archive_name
   */
  public function compress($source, $destination, $archive_name): string
  {
    Utils::verifyDirectoryExists($source);
    if (!is_dir($destination)) {
      mkdir($destination, 0777, true);
    }

    $archive = new ZipArchive();
    $filename = $archive_name.'.catrobat';

    $archive->open($destination.'/'.$filename, ZipArchive::OVERWRITE | ZipArchive::CREATE);

    $finder = new Finder();
    $finder->in($source);

    foreach ($finder as $element) {
      if ($element->isDir()) {
        $archive->addEmptyDir($element->getRelativePathname().'/');
      } elseif ($element->isFile()) {
        $archive->addFile($element->getRealpath(), $element->getRelativePathname());
      }
    }
    $archive->close();

    return $destination.'/'.$filename;
  }
}
