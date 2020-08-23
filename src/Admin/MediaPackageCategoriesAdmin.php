<?php

namespace App\Admin;

use App\Entity\MediaPackage;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaPackageCategoriesAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'adminmedia_package_category';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'media_package_category';

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('package', EntityType::class, [
        'class' => MediaPackage::class,
        'required' => true,
        'multiple' => true, ])
      ->add('priority')
    ;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
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
      ->add('name')
      ->add('package', EntityType::class, ['class' => MediaPackage::class])
      ->add('priority')
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }
}
