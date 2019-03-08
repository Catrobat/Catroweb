<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class MaintainAdmin
 * @package App\Admin
 */
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


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    //Find the implementation in the Controller-Folder
    $collection->clearExcept(['list']);
    $collection->add("apk")
      ->add("extracted")
      ->add("delete_backups")
      ->add("create_backup")
      ->add("restore_backup")
      ->add("archive_logs")
      ->add("delete_logs");
  }
}