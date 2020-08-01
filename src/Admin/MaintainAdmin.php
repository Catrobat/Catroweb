<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class MaintainAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRoutePattern = 'maintain';

  /**
   * @var string
   */
  protected $baseRouteName = 'maintain';

  protected function configureRoutes(RouteCollection $collection): void
  {
    //Find the implementation in the Controller-Folder
    $collection->clearExcept(['list']);
    $collection->add('apk')
      ->add('compressed')
      ->add('archive_logs')
      ->add('delete_logs')
    ;
  }
}
