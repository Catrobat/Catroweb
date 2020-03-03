<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class CreateExtensionsAdmin.
 */
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

  protected function configureRoutes(RouteCollection $collection)
  {
    // Find the implementation in the Controller-Folder
    $collection->clearExcept(['list']);
    $collection->add('extensions');
  }
}
