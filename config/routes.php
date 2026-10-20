<?php

declare(strict_types=1);

use App\Api\Services\OverwriteController;
use App\Application\Controller\Base\RedirectController;
use App\System\Controller\StaticController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
  $routingConfigurator->add('google_login', '/login/check-google');

  $routingConfigurator->add('facebook_login', '/login/check-facebook');

  $routingConfigurator->add('apple_login', '/login/check-apple');

  $routingConfigurator->add('gesdinet_jwt_refresh_token', '/api/authentication/refresh')
    ->controller([App\Api\Services\Authentication\JWTTokenRefreshService::class, 'refresh'])
  ;

  $routingConfigurator->import('../src/Api/OpenAPI/Server/Resources/config/routing.yaml')
    ->prefix('/api')
    ->defaults(['_format' => 'json'])
  ;

  $routingConfigurator->add('open_api_server_projects_projectsidcatrobatget', '/api/projects/{id}/catrobat')
    ->controller([OverwriteController::class, 'projectsIdCatrobatGet'])
    ->methods(['GET'])
    ->requirements(['id' => '^[a-zA-Z0-9\\\-]+$'])
  ;

  $routingConfigurator->add('open_api_server_mediaLibrary_mediaassetsiddownloadget', '/api/media/assets/{id}/download')
    ->controller([OverwriteController::class, 'mediaAssetsIdDownloadGet'])
    ->methods(['GET'])
    ->requirements(['id' => '^[a-zA-Z0-9\\\-]+$'])
  ;

  $routingConfigurator->add('protected_project_archive', '/project-files/{id}/archive')
    ->controller([OverwriteController::class, 'projectsIdCatrobatGet'])
    ->methods(['GET', 'HEAD'])
    ->requirements(['id' => '[a-zA-Z0-9-]+'])
  ;

  $routingConfigurator->add('protected_project_asset', '/project-files/{id}/assets/{path}')
    ->controller([App\Project\Controller\ProjectFilesController::class, 'asset'])
    ->methods(['GET', 'HEAD'])
    ->requirements(['id' => '[a-zA-Z0-9-]+', 'path' => '.+'])
  ;

  $routingConfigurator->add('deny_legacy_project_files', '/{resource_root}/{storage}/{path}')
    ->controller([App\Project\Controller\ProjectFilesController::class, 'deny'])
    ->defaults(['path' => ''])
    ->requirements(['resource_root' => 'resources|resources_test', 'storage' => 'programs|projects|extract|assets', 'path' => '.*'])
  ;

  $routingConfigurator->add('deny_legacy_project_extract', '/{storage}/{path}')
    ->controller([App\Project\Controller\ProjectFilesController::class, 'deny'])
    ->defaults(['path' => ''])
    ->requirements(['storage' => 'extract|programs|__protected-project-resources', 'path' => '.*'])
  ;

  $routingConfigurator->add('legacy_hour_of_code_route', '/hourOfCode')
    ->controller([RedirectController::class, 'hourOfCode'])
    ->methods(['GET'])
  ;

  $routingConfigurator->add('robots_txt_route', '/robots.txt')
    ->controller([StaticController::class, 'robotsTxt'])
    ->methods(['GET'])
  ;

  $routingConfigurator->add('catrobat_web_index', '/')
    ->controller([Symfony\Bundle\FrameworkBundle\Controller\RedirectController::class, 'redirectAction'])
    ->defaults(['route' => 'index', 'theme' => 'app'])
  ;
};
