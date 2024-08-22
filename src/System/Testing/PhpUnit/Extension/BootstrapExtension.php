<?php

declare(strict_types=1);

namespace App\System\Testing\PhpUnit\Extension;

use App\System\Commands\Helpers\CommandHelper;
use App\System\Testing\DataFixtures\DataBaseUtils;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class BootstrapExtension implements Extension
{
  public static string $CACHE_DIR = 'tests/TestData/Cache/';

  public static string $FIXTURES_DIR = 'tests/TestData/DataFixtures/';

  public static string $GENERATED_FIXTURES_DIR = 'tests/TestData/DataFixtures/GeneratedFixtures/';

  #[\Override]
  public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
  {
    $facade->registerSubscriber(new class implements StartedSubscriber {
      public function notify(Started $event): void
      {
        DataBaseUtils::recreateTestEnvironment();

        CommandHelper::executeShellCommand(
          ['bin/console', 'fos:elastic:populate'], [], 'Populate elastic'
        );
      }
    });
  }
}
