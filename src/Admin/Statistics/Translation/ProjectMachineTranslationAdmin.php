<?php

namespace App\Admin\Statistics\Translation;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class ProjectMachineTranslationAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_project_machine_translation';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'project_machine_translation';

  /**
   * {@inheritDoc}
   */
  protected function configureExportFields(): array
  {
    return ['id', 'project.id', 'project.name', 'source_language', 'target_language', 'provider', 'usage_count',
      'usage_per_month', 'last_modified_at', 'created_at', 'cached_name', 'cached_description', 'cached_credits', ];
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('project', null, ['admin_code' => 'admin.block.projects.overview'])
      ->add('source_language')
      ->add('target_language')
      ->add('provider')
      ->add('usage_count')
      ->add('usage_per_month')
      ->add('cached_name')
      ->add('cached_description')
      ->add('cached_credits')
      ->add('last_modified_at')
      ->add('created_at')
    ;
  }

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('trim')
    ;
  }
}
