<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class FileHelper
{
  /**
   * @throws \Exception
   */
  public static function verifyDirectoryExists(?string $dir): void
  {
    if (null === $dir || !is_dir($dir)) {
      throw new \Exception($dir.' is not a valid directory');
    }
  }

  public static function copyDirectory(string $src, string $dst): void
  {
    $dir = opendir($src);
    mkdir($dst);
    while (false !== ($file = readdir($dir))) {
      if ('.' === $file) {
        continue;
      }
      if ('..' === $file) {
        continue;
      }

      if (is_dir($src.'/'.$file)) {
        FileHelper::copyDirectory($src.'/'.$file, $dst.'/'.$file);
      } else {
        copy($src.'/'.$file, $dst.'/'.$file);
      }
    }

    closedir($dir);
  }

  public static function setDirectoryPermissionsRecursive(string $dir, int $mode): void
  {
    $dir = new \DirectoryIterator($dir);
    foreach ($dir as $file) {
      chmod($file->getPathname(), $mode);
      if (!$file->isDir()) {
        continue;
      }
      if ($file->isDot()) {
        continue;
      }
      FileHelper::setDirectoryPermissionsRecursive($file->getPathname(), $mode);
    }
  }

  public static function isDirectoryEmpty(string $directory_path): bool
  {
    $di = new RecursiveDirectoryIterator($directory_path, \FilesystemIterator::SKIP_DOTS);

    return 0 === iterator_count($di);
  }

  /**
   * @throws \Exception
   */
  public static function removeDirectory(string $directory_path, bool $force = false): void
  {
    self::verifyDirectoryCanBeCleared($directory_path);

    if (!is_dir($directory_path)) {
      return;
    }

    self::emptyDirectory($directory_path, $force);
    if ($force || self::isDirectoryEmpty($directory_path)) {
      rmdir($directory_path);
    }
  }

  /**
   * @throws \Exception
   */
  public static function emptyDirectory(string $directory_path, bool $force = false): void
  {
    self::verifyDirectoryCanBeCleared($directory_path);

    if (!is_dir($directory_path)) {
      return;
    }

    $files = scandir($directory_path);
    foreach ($files as $file) {
      if ('.' !== $file && '..' !== $file) {
        if ('.gitignore' === $file && !$force) {
          continue; // Keep .gitignores if not forced!
        }

        if ('dir' == filetype($directory_path.DIRECTORY_SEPARATOR.$file)) {
          self::removeDirectory($directory_path.DIRECTORY_SEPARATOR.$file);
        } else {
          unlink($directory_path.DIRECTORY_SEPARATOR.$file);
        }
      }
    }
  }

  public static function getTimestampParameter(string $filename): string
  {
    if (file_exists($filename)) {
      return '?t='.filemtime($filename);
    }

    return '';
  }

  /**
   * @throws \Exception
   */
  protected static function verifyDirectoryCanBeCleared(string $directory_path): void
  {
    foreach (self::getRemovableDirAllowList() as $allowedDir) {
      if (str_contains($directory_path, $allowedDir)) {
        return;
      }
    }

    if (!str_contains($directory_path, '..')) {
      return;
    }

    throw new \Exception('Pretty sure you should not delete this!'.$directory_path);
  }

  protected static function getRemovableDirAllowList(): array
  {
    return [
      'tests/TestData',
      'public/resources',
      'var/log',
      'var/cache',
    ];
  }
}
