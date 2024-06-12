<?php

declare(strict_types=1);

namespace App\System\Testing\DataFixtures;

use App\System\Commands\Helpers\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\ToolsException;

class DataBaseUtils
{
  protected static EntityManagerInterface $entity_manager;

  /**
   * @throws ToolsException
   */
  public static function recreateTestEnvironment(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:db:reset', '--env=test', '--no-interaction'], [], 'Reset database'
    );

    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:test:generate', '--env=test', '--no-interaction'], [], 'Generating test data'
    );
  }

  public static function databaseRollback(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:db:rollback', '--env=test', '--no-interaction'], [], 'Rollback DB'
    );

    ProjectDataFixtures::clear();
    UserDataFixtures::clear();
  }
}
