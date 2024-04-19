<?php

declare(strict_types=1);

namespace App\Admin\Tools\Logs;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class LogsAdmin extends AbstractAdmin
{
  protected $baseRoutePattern = 'logs';

  protected $baseRouteName = 'logs';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
  }
}
