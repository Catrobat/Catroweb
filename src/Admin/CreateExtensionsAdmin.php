<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class CreateExtensionsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRoutePattern = 'create';

  /**
   * @var string
   */
  protected $baseRouteName = 'create';

  protected function configureRoutes(RouteCollection $collection): void
  {
    // Find the implementation in the Controller-Folder
    $collection->clearExcept(['list']);
    $collection->add('extensions');
  }
}
