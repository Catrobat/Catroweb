<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();

  $parameters->set(
    'jenkins',
    [
      'url' => '%env(resolve:JENKINS_URL)%',
      'job' => '%env(resolve:JENKINS_JOB)%',
      'token' => '%env(resolve:JENKINS_TOKEN)%',
      'uploadtoken' => '%env(resolve:JENKINS_UPLOAD_TOKEN)%',
    ]
  );
};
