<?php

namespace App\Utils;

class Utils
{
  public static function removeDirectory(string $directory): void
  {
    foreach (glob(sprintf('%s*', $directory)) as $file)
    {
      if (is_dir($file))
      {
        self::recursiveRemoveDirectory($file);
      }
      else
      {
        unlink($file);
      }
    }
  }

  public static function getTimestampParameter(string $filename): string
  {
    if (file_exists($filename))
    {
      return '?t='.filemtime($filename);
    }

    return '';
  }

  public static function randomPassword(): string
  {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; ++$i)
    {
      $n = random_int(0, $alphaLength);
      $pass[] = $alphabet[$n];
    }

    return implode('', $pass); //turn the array into a string
  }

  private static function recursiveRemoveDirectory(string $directory): void
  {
    foreach (glob(sprintf('%s/*', $directory)) as $file)
    {
      if (is_dir($file))
      {
        self::recursiveRemoveDirectory($file);
      }
      else
      {
        unlink($file);
      }
    }
    rmdir($directory);
  }
}
