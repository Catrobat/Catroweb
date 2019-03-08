<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * Class CategoriesAdmin
 * @package App\Admin
 */
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
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('alias', TextType::class, ['label' => 'Alias'])
      ->add('programs', null, [
        'required'     => false,
        'by_reference' => false,
      ], [
        'edit'       => 'inline',
        'inline'     => 'table',
        'sortable'   => 'id',
        'admin_code' => 'catrowebadmin.block.programs.all',
      ])
      ->add('order', IntegerType::class, ['label' => 'Order']);
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('name')
      ->add('alias')
      ->add('programs', EntityType::class, ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('order');
  }
}
