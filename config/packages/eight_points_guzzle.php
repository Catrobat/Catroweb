<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'eight_points_guzzle',
    [
      'clients' => [
        'itranslate' => [
          'base_url' => 'https://api.itranslate.com',
          'options' => [
            'timeout' => 30,
            'http_errors' => false,
          ],
          'plugin' => null,
        ],
      ],
    ]
  );
};
