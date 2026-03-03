<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension('framework', [
    'rate_limiter' => [
      'report_burst' => [
        'policy' => 'no_limit',
      ],
      'report_daily' => [
        'policy' => 'no_limit',
      ],
      'comment_burst' => [
        'policy' => 'no_limit',
      ],
      'comment_daily' => [
        'policy' => 'no_limit',
      ],
      'reaction_burst' => [
        'policy' => 'no_limit',
      ],
      'follow_burst' => [
        'policy' => 'no_limit',
      ],
      'appeal_daily' => [
        'policy' => 'no_limit',
      ],
      'upload_daily' => [
        'policy' => 'no_limit',
      ],
      'auth_burst' => [
        'policy' => 'no_limit',
      ],
      'registration_burst' => [
        'policy' => 'no_limit',
      ],
      'password_reset_burst' => [
        'policy' => 'no_limit',
      ],
      'search_burst' => [
        'policy' => 'no_limit',
      ],
      'studio_create_daily' => [
        'policy' => 'no_limit',
      ],
    ],
  ]);
};
