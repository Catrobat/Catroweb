<?php

declare(strict_types=1);

use App\DB\EntityRepository\User\ResetPasswordRequestRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'symfonycasts_reset_password',
    [
      'request_password_repository' => ResetPasswordRequestRepository::class,
      'lifetime' => 3600,
      'throttle_limit' => '%reset_password.throttle_limit%',
    ]
  );
};
