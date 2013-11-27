<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\ServiceContainer;

class Extension implements \PhpSpec\Extension\ExtensionInterface
{
  public function load(ServiceContainer $container)
  {
    $container->setShared('event_dispatcher.listeners.catrobat', function ($container)
    {
      $listener = new CatrobatListener();
      $listener->setIo($container->get('console.io'));
      return $listener;
    });
  }

}
