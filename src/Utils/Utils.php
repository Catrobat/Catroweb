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
