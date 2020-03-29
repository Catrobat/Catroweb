<?php

namespace Tests\behat\context;

use App\Catrobat\Services\TestEnv\DataFixtures\ProjectDataFixtures;
use App\Catrobat\Services\TestEnv\DataFixtures\UserDataFixtures;
use App\Catrobat\Services\TestEnv\SymfonySupport;
use App\Kernel;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;

class RefreshEnvironmentContext implements KernelAwareContext
{
  use SymfonySupport;

  //--------------------------------------------------------------------------------------------------------------------
  //  Database
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeSuite
   *
   * Since we don't need to recreate the whole database for every suite we will define a preparation suite
   * in behat.yml. This preparation suite will use this hook to prepare the test database.
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
  }

  /**
   * We do the clean up before the scenarios and keep all the data after a scenario
   * to allow easy debugging at the end of an scenario. Also we need to make sure that the DB is clean anyway.
   *
   * @BeforeScenario
   *
   * @throws DBALException
   */
  public function databaseRollback(): void
  {
    $em = $this->getManager();

    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ($em->getConnection()->getSchemaManager()->listTableNames() as $tableName)
    {
      $q = $em->getConnection()->getDatabasePlatform()->getTruncateTableSql($tableName);
      $em->getConnection()->executeUpdate($q);
    }
    $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=1');

    ProjectDataFixtures::clear();
    UserDataFixtures::clear();
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  FileSystem
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Clear all files.
   *
   * @BeforeScenario
   */
  public function emptyStorage(): void
  {
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.file.extract.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.file.storage.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.screenshot.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.thumbnail.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.featuredimage.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.apk.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.backup.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.snapshot.dir'));
  }
}
