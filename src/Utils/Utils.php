<?php

namespace App\Utils;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use DirectoryIterator;
use InvalidArgumentException;

class Utils
{
  public static function verifyDirectoryExists(?string $dir): void
  {
    if (null === $dir || !is_dir($dir)) {
      throw new InvalidStorageDirectoryException($dir.' is not a valid directory');
    }
  }

  public static function copyDirectory(string $src, string $dst): void
  {
    $dir = opendir($src);
    mkdir($dst);
    while (false !== ($file = readdir($dir))) {
      if ('.' === $file || '..' === $file) {
        continue;
      }
      if (is_dir($src.'/'.$file)) {
        Utils::copyDirectory($src.'/'.$file, $dst.'/'.$file);
      } else {
        copy($src.'/'.$file, $dst.'/'.$file);
      }
    }
    closedir($dir);
  }

  public static function setDirectoryPermissionsRecursive(string $dir, int $mode): void
  {
    $dir = new DirectoryIterator($dir);
    foreach ($dir as $file) {
      chmod($file->getPathname(), $mode);
      if ($file->isDir() && !$file->isDot()) {
        Utils::setDirectoryPermissionsRecursive($file->getPathname(), $mode);
      }
    }
  }

  public static function removeDirectory(string $directory_path): void
  {
    self::emptyDirectory($directory_path);
    rmdir($directory_path);
  }

  public static function emptyDirectory(string $directory_path): void
  {
    if (!is_dir($directory_path)) {
      throw new InvalidArgumentException("{$directory_path} must be a directory");
    }

    $files = scandir($directory_path);
    foreach ($files as $file) {
      if ('.' != $file && '..' != $file) {
        if ('dir' == filetype($directory_path.DIRECTORY_SEPARATOR.$file)) {
          self::removeDirectory($directory_path.DIRECTORY_SEPARATOR.$file);
        } else {
          unlink($directory_path.DIRECTORY_SEPARATOR.$file);
        }
      }
    }
    reset($files);
  }

  public static function getTimestampParameter(string $filename): string
  {
    if (file_exists($filename)) {
      return '?t='.filemtime($filename);
    }

    return '';
  }

  public static function randomPassword(): string
  {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; ++$i) {
      $n = random_int(0, $alphaLength);
      $pass[] = $alphabet[$n];
    }

    return implode('', $pass); //turn the array into a string
  }
}
