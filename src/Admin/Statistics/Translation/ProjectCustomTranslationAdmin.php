<?php

namespace App\Admin\Statistics\Translation;

use App\DB\Entity\Project\Program;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProjectCustomTranslationAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_project_custom_translation';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'project_custom_translation';

  /**
   * @return array
   */
  public function getExportFields()
  {
    return ['id', 'project.id', 'language', 'name', 'description', 'credits'];
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
      ->add('project', null, ['admin_code' => 'admin.block.projects.overview'])
      ->add('language')
      ->add('name')
      ->add('description')
      ->add('credits')
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->add('project', EntityType::class, ['class' => Program::class], [
        'admin_code' => 'admin.block.projects.overview',
      ])
      ->add('language')
      ->add('name')
      ->add('description')
      ->add('credits')
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('acl');
  }
}
