<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'twig',
    [
      'default_path' => '%kernel.project_dir%/templates',
      'debug' => '%kernel.debug%',
      'strict_variables' => '%kernel.debug%',
      'globals' => [
        'app_version' => '%env(APP_VERSION)%',
        'app_env' => '%env(APP_ENV)%',
        'bugsnag_api_key' => '%env(BUGSNAG_API_KEY)%',
        'gtm_container_id' => '%env(GTM_CONTAINER_ID)%',
      ],
      'form_themes' => [
        '@SonataForm/Form/datepicker.html.twig',
      ],
    ]
  );
};
