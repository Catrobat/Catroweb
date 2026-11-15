<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/*
 * Background jobs: one queue per kind of work, each consumed by its own worker, so a slow job never holds up
 * another kind. Queues are rows in messenger_messages (MariaDB). Workers and the scheduler: docs/operations/Background-Jobs.md
 */
return static function (ContainerConfigurator $containerConfigurator): void {
  $queue = static fn (string $name, array $retry = []): array => [
    'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
    'options' => ['queue_name' => $name],
    'retry_strategy' => $retry + ['max_retries' => 3, 'delay' => 10_000, 'multiplier' => 3],
  ];

  $containerConfigurator->extension('framework', [
    'messenger' => [
      'failure_transport' => 'failed',
      'transports' => [
        'default' => $queue('default'),
        'scratch' => $queue('scratch', ['max_retries' => 2, 'delay' => 60_000]),
        'uploads' => $queue('uploads', ['max_retries' => 1]),
        'failed' => ['dsn' => '%env(MESSENGER_TRANSPORT_DSN)%', 'options' => ['queue_name' => 'failed']],
      ],
      'routing' => [
        Symfony\Component\Mailer\Messenger\SendEmailMessage::class => 'default',
      ],
    ],
  ]);
};
