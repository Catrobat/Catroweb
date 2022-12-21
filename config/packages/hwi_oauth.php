<?php

declare(strict_types=1);

use App\Security\OAuth\HwiOauthAccountConnector;
use App\Security\OAuth\HwiOauthRegistrationFormHandler;
use App\Security\OAuth\HwiOauthRegistrationFormType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'hwi_oauth',
    [
      'connect' => [
        'confirmation' => true,
        'account_connector' => HwiOauthAccountConnector::class,
        'registration_form_handler' => HwiOauthRegistrationFormHandler::class,
        'registration_form' => HwiOauthRegistrationFormType::class,
      ],
      'resource_owners' => [
        'google' => [
          'type' => 'google',
          'client_id' => '%env(GOOGLE_ID)%',
          'client_secret' => '%env(GOOGLE_SECRET)%',
          'scope' => 'email profile',
          'options' => [
            'display' => 'popup',
            'csrf' => true,
          ],
        ],
        'facebook' => [
          'type' => 'facebook',
          'client_id' => '%env(FB_ID)%',
          'client_secret' => '%env(FB_SECRET)%',
          'scope' => 'email',
          'options' => [
            'display' => 'popup',
            'auth_type' => 'rerequest',
            'csrf' => true,
          ],
        ],
        'apple' => [
          'type' => 'apple',
          'client_id' => '%env(APPLE_ID)%',
          'client_secret' => '%env(APPLE_SECRET)%',
          'scope' => 'name email',
          'options' => [
            'display' => 'popup',
            'csrf' => true,
          ],
        ],
      ],
    ]
  );
};
