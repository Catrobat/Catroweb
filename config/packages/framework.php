<?php

declare(strict_types=1);

use App\Application\Framework\VersionStrategy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'framework',
    [
      'secret' => '%env(APP_SECRET)%',
      'default_locale' => '%env(LOCALE)%',
      'translator' => [
        'fallback' => 'en',
      ],
      'csrf_protection' => false,
      'http_method_override' => false,
      'session' => [
        'handler_id' => null,
        'cookie_secure' => 'auto',
        'cookie_samesite' => 'lax',
        'storage_factory_id' => 'session.storage.factory.native',
      ],
      'assets' => [
        'version_strategy' => VersionStrategy::class,
        'json_manifest_path' => null,
      ],
      'handle_all_throwables' => true,
      'php_errors' => [
        'log' => true,
      ],
    ]
  );
};
