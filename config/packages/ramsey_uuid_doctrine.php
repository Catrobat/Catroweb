<?php

declare(strict_types=1);

use Ramsey\Uuid\Doctrine\UuidType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'doctrine',
    [
      'dbal' => [
        'types' => [
          'uuid' => UuidType::class,
        ],
      ],
    ]
  );
};
