<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
  $rectorConfig->paths([
    __DIR__.'/src',
    __DIR__.'/config',
  ]);

  $rectorConfig->skip([
    __DIR__.'/src/System/Testing/DataFixtures/DataBaseUtils.php',
    __DIR__.'/src/System/Commands/DBUpdater/CronJobCommand.php',
  ]);

  $rectorConfig->symfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

  $rectorConfig->sets([
    LevelSetList::UP_TO_PHP_82,
    SymfonyLevelSetList::UP_TO_SYMFONY_63,
    // SymfonySetList::SYMFONY_CODE_QUALITY,
    // SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    // DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
    // SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    // SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
    SetList::DEAD_CODE,
  ]);
};
