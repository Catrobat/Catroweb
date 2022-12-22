<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'lexik_jwt_authentication',
    [
      'secret_key' => '%env(resolve:JWT_SECRET_KEY)%',
      'public_key' => '%env(resolve:JWT_PUBLIC_KEY)%',
      'pass_phrase' => '%env(JWT_PASSPHRASE)%',
      'token_ttl' => '%env(JWT_TTL)%',
      'token_extractors' => [
        'authorization_header' => [
          'enabled' => true,
          'prefix' => 'Bearer',
          'name' => 'Authorization',
        ],
        'cookie' => [
          'enabled' => true,
          'name' => 'BEARER',
        ],
      ],
    ]
  );
};
