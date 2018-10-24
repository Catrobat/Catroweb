<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AllExtensionsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_allxtensionsadmin';
  protected $baseRoutePattern = 'all_extensions';

  protected $datagridValues = [
    '_sort_by'    => 'id',
    '_sort_order' => 'ASC',
  ];

//     Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Extension name'])
      ->add('prefix', TextType::class);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('name')
      ->add('prefix');
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('name')
      ->add('prefix')
      ->add('_action', 'actions', ['actions' => [
        'edit' => [],
      ]]);
  }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('delete')->remove('export');
  }

}
