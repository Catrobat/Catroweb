<?php

declare(strict_types=1);

namespace App\Admin\MediaPackage;

use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<MediaPackageCategory>
 */
class MediaPackageCategoriesAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminmedia_package_category';

  protected $baseRoutePattern = 'media_package_category';

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('package', EntityType::class, [
        'class' => MediaPackage::class,
        'required' => true,
        'multiple' => true, ])
      ->add('priority')
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('id')
      ->add('name')
      ->add('package', EntityType::class, ['class' => MediaPackage::class])
      ->add('priority')
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }
}
