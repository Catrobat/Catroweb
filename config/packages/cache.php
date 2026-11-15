<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'framework',
    [
      'cache' => [
        'app' => 'cache.adapter.filesystem',
        'system' => 'cache.adapter.system',
        'pools' => [
          // In the database, not var/cache: each release has its own var/cache, so a deploy's messenger:stop-workers
          // would never reach the workers started from the previous release.
          'cache.messenger.restart_workers_signal' => ['adapter' => 'cache.adapter.doctrine_dbal'],
        ],
      ],
    ]
  );
};
