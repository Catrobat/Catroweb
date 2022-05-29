<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
  $rectorConfig->paths([
    __DIR__.'/src',
  ]);

  // register a single rule
//    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

//     define sets of rules
  $rectorConfig->sets([
    LevelSetList::UP_TO_PHP_81,
  ]);

  $rectorConfig->symfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

  $rectorConfig->sets([
    SymfonyLevelSetList::UP_TO_SYMFONY_60,
    SymfonySetList::SYMFONY_CODE_QUALITY,
    SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
  ]);

  $rectorConfig->skip([__DIR__.'/src/System/Testing/DataFixtures/DataBaseUtils.php']);
};
