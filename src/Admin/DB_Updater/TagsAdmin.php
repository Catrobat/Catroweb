<?php

namespace App\Admin\DB_Updater;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TagsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_tagssadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'tags';

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_tags')
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
      ->add('projects_with_tag', 'int', ['code' => 'getProjectCount'])
    ;
  }
}
