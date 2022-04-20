<?php

namespace App\System\Testing\PhpUnit\Hook;

use App\Kernel;
use App\Storage\FileHelper;
use App\System\Commands\Helpers\CommandHelper;
use App\System\Testing\Behat\Context\RefreshEnvironmentContext;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

class RefreshTestEnvHook implements BeforeTestHook, BeforeFirstTestHook
{
  public static string $CACHE_DIR = 'tests/TestData/Cache/';
  public static string $FIXTURES_DIR = 'tests/TestData/DataFixtures/';
  public static string $GENERATED_FIXTURES_DIR = 'tests/TestData/DataFixtures/GeneratedFixtures/';

  public function executeBeforeFirstTest(): void
  {
    RefreshEnvironmentContext::prepare();

    CommandHelper::executeShellCommand(
      ['bin/console', 'fos:elastic:populate'], [], 'Populate elastic'
    );
  }

  public function executeBeforeTest(string $test): void
  {
    try {
      FileHelper::emptyDirectory(self::$CACHE_DIR);
    } catch (InvalidArgumentException $e) {
    }
  }

  // Unit tests should not need a database rollback. For performance reasons we only execute it when needed.

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public static function databaseRollback(): void
  {
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $kernel->boot();

    /* @var EntityManagerInterface $em */
    $em = $kernel->getContainer()->get('doctrine')->getManager();

    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ($em->getConnection()->createSchemaManager()->listTableNames() as $tableName) {
      $q = $em->getConnection()->getDatabasePlatform()->getTruncateTableSql($tableName);
      $em->getConnection()->executeUpdate($q);
    }
    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=1');

    ProjectDataFixtures::clear();
    UserDataFixtures::clear();
  }
}
