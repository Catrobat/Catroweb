<?php

namespace App\Admin\Tools\Logs;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class LogsAdmin extends AbstractAdmin
{
  protected $baseRoutePattern = 'logs';

  protected $baseRouteName = 'logs';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
  }
}
