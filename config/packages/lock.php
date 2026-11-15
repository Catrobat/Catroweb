<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// Scheduler lanes lock their schedule, so a second worker of the same lane stays idle.
return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension('framework', ['lock' => '%env(LOCK_DSN)%']);
};
