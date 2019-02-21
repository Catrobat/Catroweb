<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


/**
 * Class ClickStatisticsAdmin
 * @package Catrobat\AppBundle\Admin
 */
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
    '_sort_by'    => 'id',
    '_sort_order' => 'DESC',
  ];


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('type')
      ->add('program', EntityType::class, ['class' => Program::class], [
        'admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('scratch_program_id')
      ->add('recommended_from_program', EntityType::class, ['class' => Program::class],
        ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('clicked_at')
      ->add('ip')
      ->add('country_code')
      ->add('country_name')
      ->add('locale')
      ->add('user_agent')
      ->add('referrer');
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('id')
      ->add('type')
      ->add('program.name')
      ->add('scratch_program_id')
      ->add('recommended_from_program.name')
      ->add('user.username')
      ->add('ip')
      ->add('country_name')
      ->add('user_agent')
      ->add('referrer')
      ->add('locale');
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
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
        'label' => 'Tag'])
      ->add('extension.name', null, [
        'label' => 'Extension'])
      ->add('clicked_at')
      ->add('ip')
      ->add('country_code')
      ->add('country_name')
      ->add('locale')
      ->add('user_agent')
      ->add('referrer')
      ->add('_action', 'actions', ['actions' => [
        'edit' => [],
      ]]);
  }


  /**
   * @return array
   */
  public function getExportFields()
  {
    return ['id', 'type', 'user.username', 'program.id', 'program.name', 'scratch_program_id',
      'recommended_from_program.id', 'recommended_from_program.name', 'tag.en', 'extension.name',
      'clicked_at', 'ip', 'country_code', 'country_name', 'locale', 'user_agent', 'referrer'];
  }


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('create')->remove('delete');
  }
}
