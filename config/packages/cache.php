<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'framework',
    [
      'cache' => [
        // The same in every release: pool namespaces are derived from it, by default from the release's own directory,
        // so the shared restart signal below would have a different key in every release.
        'prefix_seed' => 'catroweb',
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
