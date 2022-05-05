<?php

namespace App\Admin\Tools\SendMailToUser;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class SendMailToUserAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_mail';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'mail';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }
}
