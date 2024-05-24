<?php

declare(strict_types=1);

use App\DB\Entity\Flavor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();

  $parameters->set('flavors', Flavor::ALL);
  $parameters->set('defaultFlavor', Flavor::POCKETCODE);
  $parameters->set('umbrellaTheme', 'app');
  $parameters->set('adminTheme', 'admin');
  $parameters->set(
    'themeRoutes',
    'app|pocketcode|pocketalice|pocketgalaxy|phirocode|luna|create\@school|embroidery|arduino|mindstorms'
  );
};
