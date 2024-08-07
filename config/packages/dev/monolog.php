<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension('monolog', [
    'channels' => ['search', 'download', 'soft'],
    'handlers' => [
      'main' => [
        'type' => 'fingers_crossed',
        'action_level' => 'error',
        'handler' => 'mainHandler',
        'excluded_404s' => true,
        'excluded_http_codes' => [400, 401, 402, 403, 404, 405, 406, 429],
        'buffer_size' => 50,
        'channels' => ['!soft'],
        'formatter' => 'monolog.formatter.catrobat_custom_formatter',
      ],
      'mainHandler' => [
        'type' => 'rotating_file',
        'path' => '%kernel.logs_dir%/%kernel.environment%/%kernel.environment%.log',
        'level' => 'error',
        'max_files' => 1,
      ],
      'soft' => [
        'type' => 'fingers_crossed',
        'action_level' => 'warning',
        'handler' => 'softHandler',
        'channels' => ['soft'],
      ],
      'softHandler' => [
        'type' => 'rotating_file',
        'path' => '%kernel.logs_dir%/%kernel.environment%/soft/soft.log',
        'level' => 'warning',
        'max_files' => 1,
        'formatter' => 'monolog.formatter.catrobat_custom_formatter',
      ],
      'search' => [
        'type' => 'rotating_file',
        'path' => '%kernel.logs_dir%/%kernel.environment%/search/search.log',
        'level' => 'debug',
        'max_files' => 1,
        'channels' => ['search'],
        'formatter' => 'monolog.formatter.catrobat_custom_formatter',
      ],
      'download' => [
        'type' => 'rotating_file',
        'path' => '%kernel.logs_dir%/%kernel.environment%/download/download.log',
        'level' => 'debug',
        'max_files' => 1,
        'channels' => ['download'],
        'formatter' => 'monolog.formatter.catrobat_custom_formatter',
      ],
      'console' => [
        'type' => 'console',
        'process_psr_3_messages' => false,
        'channels' => ['!event', '!doctrine', '!console'],
      ],
      'deprecation_filter' => [
        'type' => 'filter',
        'handler' => 'deprecation_stream',
        'max_level' => 'info',
        'channels' => ['php'],
      ],
      'deprecation_stream' => [
        'type' => 'stream',
        'path' => '%kernel.logs_dir%/%kernel.environment%/%kernel.environment%.deprecations.log',
      ],
      'firephp' => [
        'type' => 'firephp',
        'level' => 'warning',
      ],
      'chromephp' => [
        'type' => 'chromephp',
        'level' => 'warning',
      ],
    ],
  ]);
};
