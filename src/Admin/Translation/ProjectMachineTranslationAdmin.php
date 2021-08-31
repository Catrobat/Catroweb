<?php

namespace App\Admin\Translation;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ProjectMachineTranslationAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_project_machine_translation';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'project_machine_translation';

  /**
   * @return array
   */
  public function getExportFields()
  {
    return ['id', 'project.id', 'project.name', 'source_language', 'target_language', 'provider', 'usage_count',
      'usage_per_month', 'last_modified_at', 'created_at', ];
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('project', null, ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('source_language')
      ->add('target_language')
      ->add('provider')
      ->add('usage_count')
      ->add('usage_per_month')
      ->add('last_modified_at')
      ->add('created_at')
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('trim')
    ;
  }
}
