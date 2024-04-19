<?php

declare(strict_types=1);

use App\Api_deprecated\Security\ApiTokenAuthenticator;
use App\Security\Authentication\WebView\WebviewAuthenticator;
use App\Security\Authentication\WebView\WebviewJWTAuthenticator;
use App\Security\OAuth\HwiOauthUserProvider;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'security',
    [
      'password_hashers' => [
        UserInterface::class => 'auto',
      ],
      'providers' => [
        'chain_provider' => [
          'chain' => [
            'providers' => [
              'sonata_userbundle',
            ],
          ],
        ],
        'sonata_userbundle' => [
          'id' => 'sonata.user.security.user_provider',
        ],
      ],
      'firewalls' => [
        'api_authentication_login' => [
          'provider' => 'chain_provider',
          'pattern' => '^/api/authentication',
          'methods' => ['POST'],
          'stateless' => true,
          'json_login' => [
            'check_path' => '/api/authentication',
            'success_handler' => 'lexik_jwt_authentication.handler.authentication_success',
            'failure_handler' => 'lexik_jwt_authentication.handler.authentication_failure',
          ],
          'refresh_jwt' => [
            'check_path' => '/api/authentication/refresh',
          ],
        ],
        'api' => [
          'pattern' => '^/api',
          'provider' => 'chain_provider',
          'stateless' => true,
          'jwt' => null,
        ],
        'api_checktoken' => [
          'provider' => 'chain_provider',
          'pattern' => '^.*?/api/checkToken/check.json',
          'stateless' => true,
          'custom_authenticators' => [
            ApiTokenAuthenticator::class,
          ],
        ],
        'api_upload' => [
          'provider' => 'chain_provider',
          'pattern' => '^.*?/api/upload/upload.json',
          'custom_authenticators' => [
            ApiTokenAuthenticator::class,
          ],
        ],
        'debug' => [
          'provider' => 'chain_provider',
          'pattern' => '^/debug',
          'security' => false,
        ],
        'main' => [
          'pattern' => '^/(?!(api/))',
          'provider' => 'chain_provider',
          'stateless' => false,
          'form_login' => [
            'default_target_path' => '/',
          ],
          'custom_authenticators' => [
            WebviewAuthenticator::class,
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
            'login_path' => '/login',
            'use_forward' => false,
            'failure_path' => '/app/login',
            'success_handler' => 'catroweb.oauth_success_handler',
            'oauth_user_provider' => [
              'service' => HwiOauthUserProvider::class,
            ],
          ],
        ],
        'dev' => [
          'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
          'provider' => 'chain_provider',
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
          'path' => '^/api/user/reset-password/?$',
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
          'path' => '^/api/user/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/user/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['POST'],
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
          'path' => '^/api/project/[a-zA-Z0-9_-]+/catrobat/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/project/[a-zA-Z0-9_-]+/recommendations/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/project/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/package/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/files/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/file/[a-zA-Z0-9_-]+/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/media/files/search/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
        ],
        [
          'path' => '^/api/health/?$',
          'roles' => 'PUBLIC_ACCESS',
          'methods' => ['GET'],
          'requires_channel' => '%env(SECURE_SCHEME)%',
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
        'ROLE_ADMIN' => [
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
        'ROLE_STATISICS' => [
          'ROLE_ADMIN_BLOCK_STATISTICS_PROJECT_MACHINE_TRANSLATION_ADMIN',
          'ROLE_ADMIN_BLOCK_STATISTICS_PROJECT_CUSTOM_TRANSLATION_ADMIN',
          'ROLE_ADMIN_BLOCK_STATISTICS_COMMENT_MACHINE_TRANSLATION_ADMIN',
          'ROLE_ADMIN',
        ],
      ],
    ]
  );

  $containerConfigurator->extension(
    'acl',
    [
      'connection' => 'default',
    ]
  );
};
