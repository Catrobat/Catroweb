<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->import('@HWIOAuthBundle/Resources/config/routing/redirect.xml')
    ->prefix('/connect')
  ;

  $routingConfigurator->import('@HWIOAuthBundle/Resources/config/routing/connect.xml')
    ->prefix('/connect')
  ;

  $routingConfigurator->import('@HWIOAuthBundle/Resources/config/routing/login.xml')
    ->prefix('/login')
  ;
};
