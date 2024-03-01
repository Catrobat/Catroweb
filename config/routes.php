<?php

declare(strict_types=1);

use App\Api\Services\OverwriteController;
use App\Application\Controller\Base\RedirectController;
use App\Application\Controller\Security\RegistrationController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->add('google_login', '/login/check-google');

  $routingConfigurator->add('facebook_login', '/login/check-facebook');

  $routingConfigurator->add('apple_login', '/login/check-apple');

  $routingConfigurator->add('gesdinet_jwt_refresh_token', '/api/authentication/refresh')
    ->controller('App\Api\Services\Authentication\JWTTokenRefreshService::refresh')
  ;

  $routingConfigurator->import('@OpenAPIServerBundle/Resources/config/routing.yaml')
    ->prefix('/api')
    ->defaults(['_format' => 'json'])
  ;

  $routingConfigurator->add('open_api_server_projects_projectidcatrobatget', '/api/project/{id}/catrobat')
    ->controller([OverwriteController::class, 'projectIdCatrobatGetAction'])
    ->methods(['GET'])
    ->requirements(['id' => '^[a-zA-Z0-9\\\-]+$'])
  ;

  $routingConfigurator->add('registration_confirmation_route', '/app/verify')
    ->controller([RegistrationController::class, 'verifyUserEmail'])
    ->methods(['GET'])
  ;

  $routingConfigurator->add('legacy_hour_of_code_route', '/hourOfCode')
    ->controller([RedirectController::class, 'hourOfCodeAction'])
    ->methods(['GET'])
  ;

  $routingConfigurator->add('robots_txt_route', '/robots.txt')
    ->controller([RedirectController::class, 'robotsTxt'])
    ->methods(['GET'])
  ;

  $routingConfigurator->add('catrobat_web_index', '/')
    ->controller([Symfony\Bundle\FrameworkBundle\Controller\RedirectController::class, 'redirectAction'])
    ->defaults(['route' => 'index', 'theme' => 'app'])
  ;
};
