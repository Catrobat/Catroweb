<?php

declare(strict_types=1);

use App\System\Mail\EmailBudgetManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  // One transport per provider slot; MailerAdapter picks one per mail with the X-Transport header.
  // An unset MAILER_DSN_<NAME> falls back to null://null, so unused slots cost nothing.
  $containerConfigurator->parameters()->set('mailer_unused_dsn', 'null://null');
  $transports = [];
  foreach (EmailBudgetManager::PROVIDERS as $provider) {
    $transports[$provider] = 'main' === $provider
      ? '%env(MAILER_DSN)%'
      : '%env(default:mailer_unused_dsn:MAILER_DSN_'.strtoupper($provider).')%';
  }

  $containerConfigurator->extension(
    'framework', [
      'mailer' => [
        'transports' => $transports,
      ],
    ]
  );
};
