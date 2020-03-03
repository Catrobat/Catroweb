<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class LogsAdmin.
 */
class LogsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRoutePattern = 'logs';

  /**
   * @var string
   */
  protected $baseRouteName = 'logs';

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list']);
    $collection->add('apk')
      ->add('extracted')
      ->add('backup')
    ;
  }
}
