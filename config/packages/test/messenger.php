<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// Tests handle every job inside the request that dispatches it.
return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension('framework', [
    'messenger' => [
      'transports' => [
        'mail' => 'sync://',
        'default' => 'sync://',
        'scratch' => 'sync://',
        'uploads' => 'sync://',
        'cleanup' => 'sync://',
        'translation' => 'sync://',
        'failed' => 'in-memory://',
      ],
    ],
  ]);
};
