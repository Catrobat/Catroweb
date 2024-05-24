<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

return static function (RectorConfig $rectorConfig): void {
  $rectorConfig->paths([
    __DIR__.'/src',
    __DIR__.'/config',
  ]);

  $rectorConfig->symfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

  $rectorConfig->sets([
    DoctrineSetList::DOCTRINE_CODE_QUALITY,
  ]);

  $rectorConfig->skip([__DIR__.'/src/System/Testing/DataFixtures/DataBaseUtils.php']);
};
