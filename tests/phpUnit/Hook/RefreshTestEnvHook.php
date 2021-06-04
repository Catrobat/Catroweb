<?php

namespace Tests\phpUnit\Hook;

use App\Catrobat\Services\TestEnv\DataFixtures\ProjectDataFixtures;
use App\Catrobat\Services\TestEnv\DataFixtures\UserDataFixtures;
use App\Commands\Helpers\CommandHelper;
use App\Kernel;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;
use Tests\behat\context\RefreshEnvironmentContext;

class RefreshTestEnvHook implements BeforeTestHook, BeforeFirstTestHook
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
      Utils::emptyDirectory(self::$CACHE_DIR);
    } catch (InvalidArgumentException $e) {
    }
  }

  // Unit tests should not need a database rollback. For performance reasons we only execute it when needed.
  public static function databaseRollback(): void
  {
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $kernel->boot();

    /* @var EntityManagerInterface $em */
    $em = $kernel->getContainer()->get('doctrine')->getManager();

    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ($em->getConnection()->getSchemaManager()->listTableNames() as $tableName) {
      $q = $em->getConnection()->getDatabasePlatform()->getTruncateTableSql($tableName);
      $em->getConnection()->executeUpdate($q);
    }
    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=1');

    ProjectDataFixtures::clear();
    UserDataFixtures::clear();
  }
}
