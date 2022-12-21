<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->import('@SonataAdminBundle/Resources/config/routing/sonata_admin.xml')
    ->prefix('/admin')
  ;

  $routingConfigurator->import('@SonataUserBundle/Resources/config/routing/admin_security.xml')
    ->prefix('/admin')
  ;

  $routingConfigurator->import('@SonataUserBundle/Resources/config/routing/admin_resetting.xml')
    ->prefix('/admin/resetting')
  ;

  $routingConfigurator->add('sonata_user_admin_security_logout', '/{theme}/logout')
    ->controller('SonataUserBundle:AdminSecurity:logout')
  ;

  $routingConfigurator->import('@SonataAdminBundle/Resources/config/routing/sonata_admin.xml')
    ->prefix('/admin')
  ;

  $routingConfigurator->import('.', 'sonata_admin')
    ->prefix('/admin')
  ;
};
