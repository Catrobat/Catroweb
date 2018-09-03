<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class SnapshotAdmin extends AbstractAdmin
{
  protected $baseRoutePattern = 'snapshots';
  protected $baseRouteName = 'snapshots';

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept([
      'list',
    ]);
  }
}