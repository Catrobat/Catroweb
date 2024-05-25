<?php

declare(strict_types=1);

namespace App\Admin\Tools\FeatureFlag;

use App\DB\Entity\FeatureFlag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<FeatureFlag>
 */
class FeatureFlagAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_flag';

  protected $baseRoutePattern = 'featureflag';

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  #[\Override]
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

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
  }
}
