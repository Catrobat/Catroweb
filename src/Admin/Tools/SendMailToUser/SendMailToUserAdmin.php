<?php

namespace App\Admin\Tools\SendMailToUser;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class SendMailToUserAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_mail';

  protected $baseRoutePattern = 'mail';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }
}
