<?php

declare(strict_types=1);

use App\Translation\GoogleTranslateApi;
use App\Translation\GoogleTranslateClientAdapter;
use App\Translation\GoogleTranslateClientInterface;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $services = $containerConfigurator->services();

  $services->set(Google_Client::class, Google_Client::class)
    ->call('setDeveloperKey', ['%env(GOOGLE_API_KEY)%'])
    ->call('setClientId', ['%env(GOOGLE_CLIENT_ID)%'])
    ->call('setClientSecret', ['%env(GOOGLE_CLIENT_SECRET)%'])
  ;

  $services->set(TranslationServiceClient::class);

  $services->set(GoogleTranslateClientAdapter::class);

  $services->alias(GoogleTranslateClientInterface::class, GoogleTranslateClientAdapter::class);

  $services->set(GoogleTranslateApi::class)
    ->arg('$google_cloud_project_id', '%env(GOOGLE_CLOUD_PROJECT)%')
  ;
};
