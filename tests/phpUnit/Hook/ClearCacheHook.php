<?php

namespace Tests\phpUnit\Hook;

use PHPUnit\Runner\BeforeTestHook;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ClearCacheHook implements BeforeTestHook
{
  public static string $CACHE_DIR;

  public static string $FIXTURES_DIR;

  public static string $GENERATED_FIXTURES_DIR;

  public function __construct()
  {
    self::$CACHE_DIR = 'tests/testdata/Cache/';
    self::$FIXTURES_DIR = 'tests/testdata/DataFixtures/';
    self::$GENERATED_FIXTURES_DIR = self::$FIXTURES_DIR.'GeneratedFixtures/';
  }

  public function executeBeforeTest(string $test): void
  {
    $this->emptyDirectory(self::$CACHE_DIR);
  }

  private function emptyDirectory(string $directory): void
  {
    $filesystem = new Filesystem();

    $finder = new Finder();
    $finder->in($directory)->depth(0);
    foreach ($finder as $file)
    {
      $filesystem->remove($file);
    }
  }
}
