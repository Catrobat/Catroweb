<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->import('@HWIOAuthBundle/Resources/config/routing/redirect.php')
    ->prefix('/connect')
  ;

  $routingConfigurator->import('@HWIOAuthBundle/Resources/config/routing/connect.php')
    ->prefix('/connect')
  ;

  $routingConfigurator->import('@HWIOAuthBundle/Resources/config/routing/login.php')
    ->prefix('/login')
  ;
};
