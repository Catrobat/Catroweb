<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\ServiceContainer;

class Extension implements \PhpSpec\Extension
{
    public function load(ServiceContainer $container, array $params = [])
    {
      $container->define('event_dispatcher.listeners.catrobat', function ($container) {
      $listener = new CatrobatListener();
      $listener->setIo($container->get('console.io'));

      return $listener;
    });
    }
}
