<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'pentatrion_vite',
    [
      'build_directory' => 'build',
      // No <link rel=modulepreload>: ~30 chunks would share the line with the render-blocking CSS and
      // delay the first paint by ~0.7 s on a slow phone. The entry's imports still fetch them.
      'preload' => 'none',
      'script_attributes' => [
        'defer' => true,
      ],
    ]
  );
};
