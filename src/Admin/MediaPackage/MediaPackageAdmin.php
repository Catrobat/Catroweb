<?php

declare(strict_types=1);

namespace App\Admin\MediaPackage;

use App\DB\Entity\MediaLibrary\MediaPackage;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<MediaPackage>
 */
class MediaPackageAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminmedia_package_package';

  protected $baseRoutePattern = 'media_package';

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('name_url', TextType::class, ['label' => 'Url'])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('name')
      ->add('name_url', null, ['label' => 'Url'])
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }
}
