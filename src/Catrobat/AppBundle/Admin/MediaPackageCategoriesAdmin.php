<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class MediaPackageCategoriesAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminmedia_package_category';
  protected $baseRoutePattern = 'media_package_category';

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', 'text', ['label' => 'Name'])
      ->add('package', 'entity', ['class' => 'Catrobat\AppBundle\Entity\MediaPackage', 'required' => true, 'multiple' => true])
      ->add('priority');
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
      ->add('package', 'entity', ['class' => 'Catrobat\AppBundle\Entity\MediaPackage'])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }
}
