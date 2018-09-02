<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CategoriesAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_categoriesadmin';
  protected $baseRoutePattern = 'categories';

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', 'text', ['label' => 'Name'])
      ->add('alias', 'text', ['label' => 'Alias'])
      ->add('programs', null, [
        'required'     => false,
        'by_reference' => false,
      ], [
        'edit'       => 'inline',
        'inline'     => 'table',
        'sortable'   => 'id',
        'admin_code' => 'catrowebadmin.block.programs.all',
      ])
      ->add('order', 'integer', ['label' => 'Order']);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('name')
      ->add('alias')
      ->add('programs', 'entity', ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('order');
  }
}
