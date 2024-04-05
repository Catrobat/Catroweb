<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'gesdinet_jwt_refresh_token',
    [
      'refresh_token_class' => App\DB\Entity\RefreshToken::class,
      'ttl' => '%env(int:REFRESH_TOKEN_TTL)%',
      'token_parameter_name' => 'refresh_token',
      'single_use' => true,
    ]
  );
};
