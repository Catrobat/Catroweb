<?php

namespace App\Admin\DB_Updater;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class SpecialUpdaterAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_specialupdateradmin';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'special_updater';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_special')
    ;
  }
}
