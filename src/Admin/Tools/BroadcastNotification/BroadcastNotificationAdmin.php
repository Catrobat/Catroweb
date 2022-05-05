<?php

namespace App\Admin\Tools\BroadcastNotification;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class BroadcastNotificationAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_broadcast';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'broadcast';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }
}
