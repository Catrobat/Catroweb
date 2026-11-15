<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'framework',
    [
      'router' => [
        'strict_requirements' => null,
        // Links in mails rendered by console commands (no request to take the host from).
        'default_uri' => 'https://share.catrobat.org/',
      ],
    ]
  );
};
