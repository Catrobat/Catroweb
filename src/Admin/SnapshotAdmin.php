<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class SnapshotAdmin.
 */
class SnapshotAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRoutePattern = 'snapshots';

  /**
   * @var string
   */
  protected $baseRouteName = 'snapshots';

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept([
      'list',
    ]);
  }
}
