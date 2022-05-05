<?php

namespace App\System\Testing\Behat\Context;

use App\Kernel;
use App\Storage\FileHelper;
use App\System\Commands\Helpers\CommandHelper;
use App\System\Testing\Behat\ContextTrait;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Exception;

class RefreshEnvironmentContext implements Context
{
  use ContextTrait;

  /**
   * This hook is used to prepare the test database and generate all files.
   *
   * @BeforeSuite
   *              -> Since we don't need to recreate the whole database for every scenario we do it only once per suite.
   *                 Suites can be defined in behat.yaml.
   *
   * @throws ToolsException
   */
  public static function prepare(): void
  {
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $kernel->boot();

    /* @var EntityManagerInterface $em */
    $em = $kernel->getContainer()->get('doctrine')->getManager();
    $metaData = $em->getMetadataFactory()->getAllMetadata();
    $tool = new SchemaTool($em);
    $tool->dropSchema($metaData);
    $tool->createSchema($metaData);

    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:test:generate', '--env=test', '--no-interaction'], [], 'Generating test data'
    );
  }

  /**
   * We do the cleanup before the scenarios and keep all the data after a scenario
   * to allow easy debugging at the end of a scenario. Also, we need to make sure that the DB is clean anyway.
   *
   * @BeforeScenario
   *
   * @throws \Doctrine\DBAL\Exception
   */
  public function databaseRollback(): void
  {
    $em = $this->getManager();

    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ($em->getConnection()->createSchemaManager()->listTableNames() as $tableName) {
      $q = $em->getConnection()->getDatabasePlatform()->getTruncateTableSql($tableName);
      $em->getConnection()->executeUpdate($q);
    }
    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=1');

    ProjectDataFixtures::clear();
    UserDataFixtures::clear();
  }

  /**
   * Clear all files.
   *
   * @BeforeScenario
   *
   * @throws Exception
   */
  public function emptyStorage(): void
  {
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.file.extract.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.file.storage.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.screenshot.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.thumbnail.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.featuredimage.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.apk.dir'));
  }
}
