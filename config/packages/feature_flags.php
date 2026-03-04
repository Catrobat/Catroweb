<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();
  $parameters->set(
    'feature_flags',
    [
      'Test-Flag' => false,
      'Sidebar-Studio-Link-Feature' => false,
      'GET_projects_elastica' => false,
      'remix-graph' => false, // performance issues
      'sign-apk' => false, // not allowed by Google
    ]
  );
};
