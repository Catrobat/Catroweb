<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\Storage\FileHelper;
use App\System\Testing\Behat\ContextTrait;
use App\System\Testing\DataFixtures\DataBaseUtils;
use App\System\Testing\FakeScratchFetcher;
use Behat\Behat\Context\Context;
use Doctrine\ORM\Tools\ToolsException;
use Elastica\Query\MatchAll;
use FOS\ElasticaBundle\Index\IndexManager;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\TextUI\CliArguments\Builder as CliConfigurationBuilder;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use Symfony\Component\Dotenv\Dotenv;

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
    self::loadEnvironmentVariables();

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
      // @phpstan-ignore-next-line
      $reflection = new \ReflectionClass(Registry::class);
      $instanceProperty = $reflection->getProperty('instance');
      if (null !== $instanceProperty->getValue()) {
        return; // Already initialized
      }
    } catch (\Throwable) {
      // Reflection failed, continue to initialization
    }

    // Initialize with minimal configuration (using PHPUnit internals intentionally)
    // PHPUnit 13.4+ requires the event emitter for both, same as PHPUnit's own Application
    // @phpstan-ignore-next-line
    $emitter = EventFacade::emitter();
    // @phpstan-ignore-next-line
    $cliConfiguration = new CliConfigurationBuilder($emitter)->fromParameters([]);
    // @phpstan-ignore-next-line
    $xmlConfiguration = DefaultConfiguration::create();
    // @phpstan-ignore-next-line
    Registry::init($cliConfiguration, $xmlConfiguration, $emitter);
  }

  /**
   * Behat can start without Symfony Runtime bootstrap, so ensure .env/.env.test are loaded.
   */
  private static function loadEnvironmentVariables(): void
  {
    if (false !== getenv('DATABASE_URL') || isset($_ENV['DATABASE_URL']) || isset($_SERVER['DATABASE_URL'])) {
      return;
    }

    putenv('APP_ENV=test');
    $_ENV['APP_ENV'] = 'test';
    $_SERVER['APP_ENV'] = 'test';

    new Dotenv()->bootEnv(dirname(__DIR__, 5).'/.env');
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
   * The DB rollback does not touch Elasticsearch, so documents of the previous scenario stay searchable. When a
   * scenario then reuses fewer ids, search hits a document whose row is gone and the finder throws (500).
   *
   * @BeforeScenario
   */
  public function emptySearchIndex(): void
  {
    /** @var IndexManager $index_manager */
    $index_manager = $this->getContainer()->get('fos_elastica.index_manager');
    foreach ($index_manager->getAllIndexes() as $index) {
      $index->deleteByQuery(new MatchAll(), ['refresh' => true, 'conflicts' => 'proceed']);
    }
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
    FakeScratchFetcher::reset();
  }
}
