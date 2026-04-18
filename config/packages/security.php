<?php

declare(strict_types=1);

use App\DB\Entity\User\User;
use App\Security\Authentication\ApiAuthenticationSuccessHandler;
use App\Security\Authentication\FormLoginSuccessHandler;
use App\Security\Authentication\JwtRefresh\ApiRefreshTokenSuccessHandler;
use App\Security\Authentication\WebView\WebviewJWTAuthenticator;
use App\Security\OAuth\HwiOauthUserProvider;
use App\Security\OAuth\OAuthSuccessHandler;
use App\Security\SuspendedUserChecker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\UserInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'security',
    [
      'password_hashers' => [
        UserInterface::class => 'auto',
      ],
      'providers' => [
        'user_provider' => [
          'entity' => [
            'class' => User::class,
            'property' => 'username',
          ],
        ],
      ],
      'firewalls' => [
        'api_authentication_login' => [
          'provider' => 'user_provider',
          'user_checker' => SuspendedUserChecker::class,
          'pattern' => '^/api/authentication',
          'methods' => ['POST'],
          'stateless' => true,
          'json_login' => [
            'check_path' => '/api/authentication',
            'success_handler' => ApiAuthenticationSuccessHandler::class,
            'failure_handler' => 'lexik_jwt_authentication.handler.authentication_failure',
          ],
          'refresh_jwt' => [
            'check_path' => '/api/authentication/refresh',
            'success_handler' => ApiRefreshTokenSuccessHandler::class,
          ],
        ],
        'api' => [
          'pattern' => '^/api',
          'provider' => 'user_provider',
          'stateless' => true,
          'jwt' => null,
        ],
        'debug' => [
          'provider' => 'user_provider',
          'pattern' => '^/debug',
          'security' => false,
        ],
        'main' => [
          'pattern' => '^/(?!(api/))',
          'provider' => 'user_provider',
          'user_checker' => SuspendedUserChecker::class,
          'stateless' => false,
          'form_login' => [
            'login_path' => '/app/login',
            'default_target_path' => '/',
            'success_handler' => FormLoginSuccessHandler::class,
            'enable_csrf' => true,
          ],
          'custom_authenticators' => [
            WebviewJWTAuthenticator::class,
          ],
          'entry_point' => WebviewJWTAuthenticator::class,
          'oauth' => [
            'remember_me' => true,
            'resource_owners' => [
              'google' => '/login/check-google',
              'facebook' => '/login/check-facebook',
              'apple' => '/login/check-apple',
            ],
            'login_path' => '/app/login',
            'use_forward' => false,
            'failure_path' => '/app/login',
            'success_handler' => OAuthSuccessHandler::class,
            'oauth_user_provider' => [
              'service' => HwiOauthUserProvider::class,
            ],
          ],
        ],
        'dev' => [
          'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
          'provider' => 'user_provider',
          'security' => false,
          'form_login' => [
            'always_use_default_target_path' => true,
            'default_target_path' => '/user',
          ],
          'logout' => true,
        ],
      ],
      'access_control' => [
        [
          'path' => '^/api/authentication/refresh/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/authentication/upgrade/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/authentication/oauth/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/authentication/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/reset-password/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/search/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/me/data-export/?$',
          'roles' => 'IS_AUTHENTICATED_FULLY',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/me/reports/?$',
          'roles' => 'IS_AUTHENTICATED_FULLY',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/me/?$',
          'roles' => 'IS_AUTHENTICATED_FULLY',
          'methods' => ['GET', 'PATCH', 'DELETE'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/featured-banners/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/studios/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/studios/[a-zA-Z0-9_-]+/members/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/studios/[a-zA-Z0-9_-]+/projects/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/studios/[a-zA-Z0-9_-]+/comments/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/studios/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/featured/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/categories/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/search/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/user/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/extensions/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/tags/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/catrobat/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/recommendations/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/library/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/categories/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/categories/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/assets/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/assets/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/assets/[a-zA-Z0-9_-]+/download/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/health/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
        ],
        [
          'path' => '^/api/survey/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/search/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/code/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/code-statistics/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/remix-graph/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/reactions/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/reactions/users/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/projects/[a-zA-Z0-9_-]+/comments/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/comments/[a-zA-Z0-9-]+/replies/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/comments/[a-zA-Z0-9-]+/translation/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/[a-zA-Z0-9_-]+/followers/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/[a-zA-Z0-9_-]+/following/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/[a-zA-Z0-9_-]+/achievements/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/users/[a-zA-Z0-9_-]+/studios/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/moderation/',
          'roles' => 'ROLE_ADMIN',
          'methods' => ['GET', 'PUT'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api',
          'roles' => 'IS_AUTHENTICATED_FULLY',
        ],
        [
          'path' => '^.*?/api/checkToken/check.json',
          'roles' => 'IS_AUTHENTICATED_FULLY',
        ],
        [
          'path' => '^.*?/api/upload/upload.json',
          'roles' => 'IS_AUTHENTICATED_FULLY',
        ],
        [
          'path' => '^/system/',
          'role' => [
            'ROLE_ADMIN',
            'ROLE_SONATA_ADMIN',
          ],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/admin/',
          'role' => [
            'ROLE_ADMIN',
            'ROLE_SONATA_ADMIN',
          ],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/.*',
          'role' => 'PUBLIC_ACCESS',
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
      ],
      'role_hierarchy' => [
        'ROLE_MODERATOR' => [
          'ROLE_USER',
        ],
        'ROLE_ADMIN' => [
          'ROLE_MODERATOR',
          'ROLE_USER',
          'ROLE_SONATA_ADMIN',
        ],
        'ROLE_SUPER_ADMIN' => [
          'ROLE_ADMIN',
          'ROLE_ALLOWED_TO_SWITCH',
        ],
        'ROLE_FEATURED' => [
          'ROLE_ADMIN_BLOCK_FEATURED_PROGRAM_ADMIN',
          'ROLE_ADMIN_BLOCK_PROGRAMS_CATEGORIES_ADMIN',
          'ROLE_ADMIN',
        ],
        'ROLE_APPROVE' => [
          'ROLE_ADMIN_BLOCK_PROGRAMS_ALL_ADMIN',
          'ROLE_ADMIN_BLOCK_PROGRAMS_APPROVE_ADMIN',
          'ROLE_ADMIN_BLOCK_PROGRAMS_REPORTED_ADMIN',
          'ROLE_ADMIN_BLOCK_PROGRAMS_COMMENTS_ADMIN',
          'ROLE_ADMIN',
        ],
        'ROLE_MEDIAPACKAGE' => [
          'ROLE_ADMIN_BLOCK_MEDIAPACKAGE_FILE_ADMIN',
          'ROLE_ADMIN_BLOCK_MEDIAPACKAGE_CATEGORY_ADMIN',
          'ROLE_ADMIN_BLOCK_MEDIAPACKAGE_PACKAGE_ADMIN',
          'ROLE_ADMIN',
        ],
        'ROLE_STATISTICS' => [
          'ROLE_ADMIN_BLOCK_STATISTICS_PROJECT_MACHINE_TRANSLATION_ADMIN',
          'ROLE_ADMIN_BLOCK_STATISTICS_PROJECT_CUSTOM_TRANSLATION_ADMIN',
          'ROLE_ADMIN_BLOCK_STATISTICS_COMMENT_MACHINE_TRANSLATION_ADMIN',
          'ROLE_ADMIN',
        ],
      ],
    ]
  );
};
