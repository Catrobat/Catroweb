<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()

  ->withPaths([
    __DIR__.'/src',
    __DIR__.'/config',
    __DIR__.'/tests',
  ])

  ->withPhpSets(php83: true)

  // here we can define, what prepared sets of rules will be applied
  ->withPreparedSets(
    deadCode: true,
    codeQuality: true,
    typeDeclarations: true,
    earlyReturn: true,
    strictBooleans: true,
  )
;
