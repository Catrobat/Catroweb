<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'httplug',
    [
      'plugins' => [
        'retry' => [
          'retry' => 1,
        ],
      ],
      'discovery' => [
        'client' => 'auto',
      ],
      'clients' => [
        'app' => [
          'http_methods_client' => true,
          'plugins' => [
            'httplug.plugin.content_length',
            'httplug.plugin.redirect',
          ],
        ],
      ],
    ]
  );
};
