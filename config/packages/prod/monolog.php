<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'monolog',
    [
      'channels' => [
        'search',
        'download',
        'upload',
        'login',
      ],
      'handlers' => [
        'main' => [
          'type' => 'fingers_crossed',
          'action_level' => 'error',
          'handler' => 'mainHandler',
          'excluded_http_codes' => [400, 401, 402, 403, 404, 405, 406, 429],
          'buffer_size' => 50,
          'channels' => ['!login'],
        ],
        'mainHandler' => [
          'type' => 'rotating_file',
          'formatter' => 'monolog.formatter.catrobat_custom_formatter',
          'path' => '%kernel.logs_dir%/%kernel.environment%/%kernel.environment%.log',
          'level' => 'info',
          'max_files' => 14,
          'include_stacktraces' => true,
        ],
        'search' => [
          'type' => 'rotating_file',
          'path' => '%kernel.logs_dir%/%kernel.environment%/search/search.log',
          'level' => 'debug',
          'max_files' => 3,
          'channels' => [
            'search',
          ],
          'formatter' => 'monolog.formatter.catrobat_custom_formatter',
        ],
        'download' => [
          'type' => 'rotating_file',
          'path' => '%kernel.logs_dir%/%kernel.environment%/download/download.log',
          'level' => 'debug',
          'max_files' => 3,
          'channels' => [
            'download',
          ],
          'formatter' => 'monolog.formatter.catrobat_custom_formatter',
        ],
        'upload' => [
          'type' => 'rotating_file',
          'path' => '%kernel.logs_dir%/%kernel.environment%/upload/upload.log',
          'level' => 'info',
          'max_files' => 30,
          'channels' => ['upload'],
          'formatter' => 'monolog.formatter.catrobat_custom_formatter',
        ],
        // Top level of var/log/prod, so promtail ships it to Loki (job="catroweb-app").
        // Own formatter: these lines must never carry the client IP or a user name.
        'login' => [
          'type' => 'rotating_file',
          'path' => '%kernel.logs_dir%/%kernel.environment%/login.log',
          'level' => 'info',
          'max_files' => 30,
          'channels' => ['login'],
          'formatter' => 'monolog.formatter.login_formatter',
        ],
        'console' => [
          'type' => 'console',
          'process_psr_3_messages' => false,
          'channels' => [
            '!event',
            '!doctrine',
            '!console',
          ],
        ],
        'deprecation_filter' => [
          'type' => 'filter',
          'handler' => 'deprecation_stream',
          'max_level' => 'info',
          'channels' => [
            'php',
          ],
        ],
        // Rotated: as a plain stream it grew without bound (188 MB in prod by 2026-11).
        'deprecation_stream' => [
          'type' => 'rotating_file',
          'path' => '%kernel.logs_dir%/%kernel.environment%/%kernel.environment%.deprecations.log',
          'max_files' => 3,
        ],
      ],
    ]
  );
};
