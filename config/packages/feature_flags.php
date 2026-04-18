<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();
  $parameters->set(
    'feature_flags',
    [
      'Test-Flag' => false,
      'GET_projects_elastica' => false,
      'force_webview' => false,
    ]
  );
};
