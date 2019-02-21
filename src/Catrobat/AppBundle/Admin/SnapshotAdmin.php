<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class SnapshotAdmin
 * @package Catrobat\AppBundle\Admin
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