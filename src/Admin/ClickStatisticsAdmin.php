<?php

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ClickStatisticsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_clickstatisticsadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'click_stats';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'id',
    '_sort_order' => 'DESC',
  ];

  /**
   * @return array
   */
  public function getExportFields()
  {
    return ['id', 'type', 'user.username', 'program.id', 'program.name', 'scratch_program_id',
      'recommended_from_program.id', 'recommended_from_program.name', 'tag.en', 'extension.name',
      'clicked_at', 'locale', 'user_agent', 'referrer', ];
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
    $datagridMapper
      ->add('id')
      ->add('type')
      ->add('program.name')
      ->add('scratch_program_id')
      ->add('recommended_from_program.name')
      ->add('user.username')
      ->add('user_agent')
      ->add('referrer')
      ->add('locale')
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $listMapper
      ->addIdentifier('id')
      ->add('type')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('program', EntityType::class, ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('scratch_program_id')
      ->add('recommended_from_program',
        EntityType::class, ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('tag.en', null, [
        'label' => 'Tag', ])
      ->add('extension.name', null, [
        'label' => 'Extension', ])
      ->add('clicked_at')
      ->add('locale')
      ->add('user_agent')
      ->add('referrer')
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete');
  }
}
