<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'files',
    [
      [
        'source' => '/translations/catroweb.en.yaml',
        'translation' => '/translations/catroweb.%locale_with_underscore%.yaml',
        'dest' => '/catroweb/translations/catroweb.en.yaml',
        'type' => 'yaml',
        'update_option' => 'update_as_unapproved',
      ],
    ]
  );
};
