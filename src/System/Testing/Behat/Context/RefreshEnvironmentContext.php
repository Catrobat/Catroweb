<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\Storage\FileHelper;
use App\System\Testing\Behat\ContextTrait;
use App\System\Testing\DataFixtures\DataBaseUtils;
use Behat\Behat\Context\Context;
use Doctrine\ORM\Tools\ToolsException;
use PHPUnit\TextUI\CliArguments\Builder as CliConfigurationBuilder;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

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
    // Initialize PHPUnit Configuration for use with PHPUnit\Framework\Assert in Behat context
    // PHPUnit 12+ requires the Configuration Registry to be initialized before using Assert
    self::initializePHPUnitConfiguration();

    DataBaseUtils::recreateTestEnvironment();
  }

  /**
   * Initialize PHPUnit's Configuration Registry for use outside of PHPUnit test runner.
   * This is required for PHPUnit 12+ when using PHPUnit\Framework\Assert in Behat contexts.
   *
   * @psalm-suppress InternalClass
   * @psalm-suppress InternalMethod
   */
  private static function initializePHPUnitConfiguration(): void
  {
    try {
      // Check if already initialized by using reflection to avoid the assert() in Registry::get()
      $reflection = new \ReflectionClass(Registry::class);
      $instanceProperty = $reflection->getProperty('instance');
      if (null !== $instanceProperty->getValue()) {
        return; // Already initialized
      }
    } catch (\Throwable) {
      // Reflection failed, continue to initialization
    }

    // Initialize with minimal configuration (using PHPUnit internals intentionally)
    $cliConfiguration = (new CliConfigurationBuilder())->fromParameters([]);
    $xmlConfiguration = DefaultConfiguration::create();
    Registry::init($cliConfiguration, $xmlConfiguration);
  }

  /**
   * We do the cleanup before the scenarios and keep all the data after a scenario
   * to allow easy debugging at the end of a scenario. Also, we need to make sure that the DB is clean anyway.
   *
   * @BeforeScenario
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
