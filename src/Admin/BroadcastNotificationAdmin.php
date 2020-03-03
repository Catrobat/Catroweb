<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class BroadcastNotificationAdmin.
 */
class BroadcastNotificationAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_broadcast';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'broadcast';

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }
}
