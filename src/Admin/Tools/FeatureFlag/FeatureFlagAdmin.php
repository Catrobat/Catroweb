<?php

namespace App\Admin\Tools\FeatureFlag;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class FeatureFlagAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_flag';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'featureflag';

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', 'text')
      ->add('value', 'checkbox')
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
      ->addIdentifier('id')
      ->add('name')
      ->add('value', 'choice', [
        'editable' => true,
        'choices' => [
          0 => 'False',
          1 => 'True',
        ],
      ])
    ;
  }

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
  }
}
