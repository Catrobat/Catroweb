<?php

namespace App\Admin\DB_Updater;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ExtensionsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_extensionssadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'extensions';

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_extensions')
    ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('internal_title')
      ->add('enabled')
      ->add('projects_with_extensions', 'int', ['code' => 'getProjectCount'])
    ;
  }
}
