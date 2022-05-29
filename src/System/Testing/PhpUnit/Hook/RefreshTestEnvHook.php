<?php

namespace App\System\Testing\PhpUnit\Hook;

use App\Storage\FileHelper;
use App\System\Commands\Helpers\CommandHelper;
use App\System\Testing\DataFixtures\DataBaseUtils;
use InvalidArgumentException;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

class RefreshTestEnvHook implements BeforeTestHook, BeforeFirstTestHook
{
  public static string $CACHE_DIR = 'tests/TestData/Cache/';
  public static string $FIXTURES_DIR = 'tests/TestData/DataFixtures/';
  public static string $GENERATED_FIXTURES_DIR = 'tests/TestData/DataFixtures/GeneratedFixtures/';

  /**
   * @throws \Doctrine\ORM\Tools\ToolsException
   */
  public function executeBeforeFirstTest(): void
  {
    DataBaseUtils::recreateTestEnvironment();

    CommandHelper::executeShellCommand(
      ['bin/console', 'fos:elastic:populate'], [], 'Populate elastic'
    );
  }

  public function executeBeforeTest(string $test): void
  {
    try {
      FileHelper::emptyDirectory(self::$CACHE_DIR);
    } catch (InvalidArgumentException) {
    }
  }

  // Unit tests should not need a database rollback. For performance reasons we only execute it when needed.

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public function databaseRollback(): void
  {
    DataBaseUtils::databaseRollback();
  }
}
