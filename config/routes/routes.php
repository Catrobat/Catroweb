<?php

declare(strict_types=1);

use App\Application\Controller\Docs\DocsController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->import('../../src/Application/Controller', 'attribute')
    ->prefix('/{theme}/')
    ->requirements(['theme' => '%themeRoutes%'])
  ;

  $routingConfigurator->add('docs_llms_txt', '/llms.txt')
    ->controller([DocsController::class, 'llmsTxt'])
    ->methods(['GET'])
  ;

  $routingConfigurator->import('../../src/System/Controller', 'attribute')
    ->prefix('/{theme}/')
    ->requirements(['theme' => 'system'])
  ;

  $routingConfigurator->import('../../src/Admin/', 'attribute')
    ->prefix('/admin/')
    ->requirements(['theme' => 'admin'])
  ;
};
