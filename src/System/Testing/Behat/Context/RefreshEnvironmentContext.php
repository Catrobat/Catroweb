<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\Storage\FileHelper;
use App\System\Testing\Behat\ContextTrait;
use App\System\Testing\DataFixtures\DataBaseUtils;
use Behat\Behat\Context\Context;
use Doctrine\ORM\Tools\ToolsException;

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
    DataBaseUtils::recreateTestEnvironment();
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
    DataBaseUtils::databaseRollback();
  }

  /**
   * Clear all files.
   *
   * @BeforeScenario
   *
   * @throws \Exception
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
