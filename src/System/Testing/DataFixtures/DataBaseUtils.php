<?php

declare(strict_types=1);

namespace App\System\Testing\DataFixtures;

use App\Kernel;
use App\System\Commands\Helpers\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Component\HttpKernel\KernelInterface;

class DataBaseUtils
{
  protected static ?KernelInterface $kernel = null;

  /**
   * @throws ToolsException
   */
  public static function recreateTestEnvironment(): void
  {
    $entity_manager = self::getEntityManager();
    $metaData = $entity_manager->getMetadataFactory()->getAllMetadata();
    $tool = new SchemaTool($entity_manager);
    $tool->dropSchema($metaData);
    $tool->createSchema($metaData);

    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:test:generate', '--env=test', '--no-interaction'], [], 'Generating test data'
    );
  }

  /**
   * @throws \Doctrine\DBAL\Exception
   */
  public static function databaseRollback(): void
  {
    $entity_manager = self::getEntityManager();
    $entity_manager->getConnection()->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ($entity_manager->getConnection()->createSchemaManager()->listTableNames() as $tableName) {
      $q = $entity_manager->getConnection()->getDatabasePlatform()->getTruncateTableSql($tableName);
      $entity_manager->getConnection()->executeUpdate($q);
    }
    $entity_manager->getConnection()->query('SET FOREIGN_KEY_CHECKS=1');

    ProjectDataFixtures::clear();
    UserDataFixtures::clear();
  }

  protected static function getEntityManager(): ?EntityManagerInterface
  {
    return self::getKernel()->getContainer()->get('doctrine.orm.entity_manager');
  }

  protected static function getKernel(): KernelInterface
  {
    if (is_null(self::$kernel)) {
      self::$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
      self::$kernel->boot();
    }

    return self::$kernel;
  }
}
