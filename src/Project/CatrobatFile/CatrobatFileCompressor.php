<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Storage\FileHelper;
use Symfony\Component\Finder\Finder;

class CatrobatFileCompressor
{
  /**
   * @throws \Exception
   */
  public function compress(string $source, string $destination, string $archive_name): string
  {
    FileHelper::verifyDirectoryExists($source);
    if (!is_dir($destination)) {
      mkdir($destination, 0777, true);
    }

    $archive = new \ZipArchive();
    $filename = $archive_name.'.catrobat';

    $archive->open($destination.'/'.$filename, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);

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
