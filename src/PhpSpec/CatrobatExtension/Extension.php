<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\ServiceContainer;

/**
 * Class Extension
 * @package PhpSpec\CatrobatExtension
 */
class Extension implements \PhpSpec\Extension
{
  /**
   * @param ServiceContainer $container
   * @param array            $params
   */
  public function load(ServiceContainer $container, array $params = [])
  {
    $container->define('event_dispatcher.listeners.catrobat', function ($container) {
      $listener = new CatrobatListener();
      $listener->setIo($container->get('console.io'));

      return $listener;
    }, ['event_dispatcher.listeners']);
  }
}
