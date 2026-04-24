<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'doctrine',
    [
      'orm' => [
        'query_cache_driver' => [
          'type' => 'pool',
          'pool' => 'doctrine.system_cache_pool',
        ],
        'result_cache_driver' => [
          'type' => 'pool',
          'pool' => 'doctrine.result_cache_pool',
        ],
      ],
    ]
  );

  $containerConfigurator->extension(
    'framework',
    [
      'cache' => [
        'app' => 'cache.adapter.redis',
        'default_redis_provider' => '%env(REDIS_URL)%',
        'pools' => [
          'doctrine.result_cache_pool' => [
            'adapter' => 'cache.app',
          ],
          'doctrine.system_cache_pool' => [
            'adapter' => 'cache.system',
          ],
        ],
      ],
    ]
  );
};
