<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension('framework', [
    'rate_limiter' => [
      'report_burst' => [
        'policy' => 'sliding_window',
        'limit' => 3,
        'interval' => '15 minutes',
      ],
      'report_daily' => [
        'policy' => 'sliding_window',
        'limit' => 10,
        'interval' => '24 hours',
      ],
      'comment_burst' => [
        'policy' => 'sliding_window',
        'limit' => 5,
        'interval' => '5 minutes',
      ],
      'comment_daily' => [
        'policy' => 'sliding_window',
        'limit' => 50,
        'interval' => '24 hours',
      ],
      'reaction_burst' => [
        'policy' => 'sliding_window',
        'limit' => 30,
        'interval' => '5 minutes',
      ],
      'follow_burst' => [
        'policy' => 'sliding_window',
        'limit' => 20,
        'interval' => '5 minutes',
      ],
      'appeal_daily' => [
        'policy' => 'sliding_window',
        'limit' => 3,
        'interval' => '24 hours',
      ],
      'upload_daily' => [
        'policy' => 'sliding_window',
        'limit' => 10,
        'interval' => '24 hours',
      ],
      'auth_burst' => [
        'policy' => 'sliding_window',
        'limit' => 10,
        'interval' => '15 minutes',
      ],
      'registration_burst' => [
        'policy' => 'sliding_window',
        'limit' => 3,
        'interval' => '1 hour',
      ],
      'password_reset_burst' => [
        'policy' => 'sliding_window',
        'limit' => 5,
        'interval' => '1 hour',
      ],
      'search_burst' => [
        'policy' => 'sliding_window',
        'limit' => 30,
        'interval' => '1 minute',
      ],
      'studio_create_daily' => [
        'policy' => 'sliding_window',
        'limit' => 5,
        'interval' => '24 hours',
      ],
      'studio_comment_burst' => [
        'policy' => 'sliding_window',
        'limit' => 10,
        'interval' => '5 minutes',
      ],
      'notification_burst' => [
        'policy' => 'sliding_window',
        'limit' => 30,
        'interval' => '1 minute',
      ],
      'achievement_burst' => [
        'policy' => 'sliding_window',
        'limit' => 30,
        'interval' => '1 minute',
      ],
      'media_library_burst' => [
        'policy' => 'sliding_window',
        'limit' => 60,
        'interval' => '1 minute',
      ],
      'download_burst' => [
        'policy' => 'sliding_window',
        'limit' => 10,
        'interval' => '1 minute',
      ],
      'moderation_admin_burst' => [
        'policy' => 'sliding_window',
        'limit' => 60,
        'interval' => '1 minute',
      ],
      'data_export_daily' => [
        'policy' => 'sliding_window',
        'limit' => 3,
        'interval' => '24 hours',
      ],
    ],
  ]);
};
