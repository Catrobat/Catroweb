<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class SnapshotAdmin
 * @package App\Admin
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


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept([
      'list',
    ]);
  }
}