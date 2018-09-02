<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class MediaPackageAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminmedia_package_package';
  protected $baseRoutePattern = 'media_package';

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', 'text', ['label' => 'Name'])
      ->add('name_url', 'text', ['label' => 'Url']);
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
      ->add('name_url', null, ["label" => "Url"])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }
}
