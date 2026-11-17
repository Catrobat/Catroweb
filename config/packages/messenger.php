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
    'retry_strategy' => $retry + ['max_retries' => 3, 'delay' => 10_000, 'multiplier' => 3, 'max_delay' => 600_000],
  ];

  $containerConfigurator->extension('framework', [
    'messenger' => [
      'failure_transport' => 'failed',
      'transports' => [
        'mail' => $queue('mail'),
        'default' => $queue('default'),
        'scratch' => $queue('scratch', ['max_retries' => 2, 'delay' => 60_000]),
        'uploads' => $queue('uploads', ['max_retries' => 1]),
        // Bulk clean-ups that chain one batch after the next for as long as there is work; their own worker.
        'cleanup' => $queue('cleanup'),
        // Visitors wait on these: no retry (the page has given up by then), and a backlog never delays moderation scans.
        'translation' => $queue('translation', ['max_retries' => 0]),
        'failed' => ['dsn' => '%env(MESSENGER_TRANSPORT_DSN)%', 'options' => ['queue_name' => 'failed']],
      ],
      'routing' => [
        // Its own queue: a verification mail never waits behind a backlog of code view exports.
        App\System\Mail\SendMail::class => 'mail',
        App\Project\Scratch\ConvertScratchProject::class => 'scratch',
        App\Project\Upload\CompleteProjectUpload::class => 'uploads',
        App\Project\CodeView\ExportCodeView::class => 'default',
        App\Translation\TranslateProject::class => 'translation',
        App\User\Notification\NotifyFollowersOfNewProject::class => 'default',
        App\Moderation\ScanContentImages::class => 'default',
        App\Project\GarbageCollectOrphanedAssets::class => 'cleanup',
      ],
    ],
  ]);
};
