<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class CreateExtensionsAdmin extends AbstractAdmin
{
  protected $baseRoutePattern = 'create';
  protected $baseRouteName = 'create';

  //Find the implementation in the Controller-Folder
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list']);
    $collection->add("extensions");
  }
}