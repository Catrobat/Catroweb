<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();

  $parameters->set(
    'flavors',
    [
      'pocketcode',
      'pocketalice',
      'pocketgalaxy',
      'phirocode',
      'luna',
      'create@school',
      'embroidery',
      'arduino',
      'mindstorms',
    ]
  );
  $parameters->set('defaultFlavor', 'pocketcode');
  $parameters->set('umbrellaTheme', 'app');
  $parameters->set('adminTheme', 'admin');
  $parameters->set(
    'themeRoutes',
    'app|pocketcode|pocketalice|pocketgalaxy|phirocode|luna|create\@school|embroidery|arduino|mindstorms'
  );
};
