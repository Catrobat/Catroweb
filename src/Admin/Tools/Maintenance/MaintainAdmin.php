<?php

declare(strict_types=1);

namespace App\Admin\Tools\Maintenance;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class MaintainAdmin extends AbstractAdmin
{
  protected $baseRoutePattern = 'maintain';

  protected $baseRouteName = 'maintain';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    // Find the implementation in the Controller-Folder
    $collection->clearExcept(['list']);
    $collection->add('apk')
      ->add('compressed')
      ->add('archive_logs')
      ->add('delete_logs')
    ;
  }
}
