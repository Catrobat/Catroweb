<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->import('../../src/Application/Controller', 'annotation')
    ->prefix('/{theme}/')
    ->requirements(['theme' => '%themeRoutes%'])
  ;

  $routingConfigurator->import('../../src/System/Controller', 'annotation')
    ->prefix('/{theme}/')
    ->requirements(['theme' => 'system'])
  ;

  $routingConfigurator->import('../../src/Admin/', 'annotation')
    ->prefix('/admin/')
    ->requirements(['theme' => 'admin'])
  ;

  $routingConfigurator->import('../../src/Api_deprecated/Controller', 'annotation')
    ->prefix('/{theme}/')
    ->requirements(['theme' => '%themeRoutes%'])
  ;
};
