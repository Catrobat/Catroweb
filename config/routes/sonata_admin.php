<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->import('@SonataAdminBundle/Resources/config/routing/sonata_admin.xml')
    ->prefix('/admin')
  ;

  $routingConfigurator->import('@SonataAdminBundle/Resources/config/routing/sonata_admin.xml')
    ->prefix('/admin')
  ;

  $routingConfigurator->import('.', 'sonata_admin')
    ->prefix('/admin')
  ;
};
