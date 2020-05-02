<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CategoriesAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_categoriesadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'categories';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'order',
    '_sort_order' => 'ASC',
  ];

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('alias', TextType::class, ['label' => 'Alias'])
      ->add('programs', null, [
        'required' => false,
        'by_reference' => false,
      ], [
        'edit' => 'inline',
        'inline' => 'table',
        'sortable' => 'id',
        'admin_code' => 'catrowebadmin.block.programs.all',
      ])
      ->add('order', IntegerType::class, ['label' => 'Order'])
    ;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
    $datagridMapper
      ->add('name', null, ['label' => 'Starter Category'])
      ->add('alias', null, ['label' => 'Category Alias'])
      ->add('programs', null, ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('order')
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
      ->add('name', null, ['label' => 'Starter Category', 'sortable' => false])
      ->add('alias', null, ['label' => 'Category Alias', 'sortable' => false])
      ->add('programs', EntityType::class, ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('order')
      ->add('_action', 'action', ['actions' => [
        'edit' => [],
        'removeFromStarterTable' => ['template' => 'Admin/CRUD/list__action_remove_from_starter_table.html.twig'],
      ]])
    ;
  }

  /**
   * @param RouteCollection $collection
   *
   * Routes to be added/removed
   */
  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection
      ->add('removeFromStarterTable')
      ->remove('delete')
    ;
  }
}
