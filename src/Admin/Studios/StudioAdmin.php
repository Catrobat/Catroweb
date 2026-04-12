<?php

declare(strict_types=1);

namespace App\Admin\Studios;

use App\DB\Entity\Studio\Studio;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;

/**
 * @phpstan-extends AbstractAdmin<Studio>
 */
class StudioAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_studios';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'studio';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'created_on';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('name')
      ->add('is_public')
      ->add('is_enabled')
      ->add('allow_comments')
      ->add('created_on', DateTimeRangeFilter::class, [
        'field_type' => DateTimeRangePickerType::class,
        'label' => 'Created On',
      ])
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('name')
      ->add('id')
      ->add('is_public', null, ['label' => 'Public'])
      ->add('is_enabled', null, ['label' => 'Enabled'])
      ->add('allow_comments', null, ['label' => 'Comments'])
      ->add('created_on', null, ['label' => 'Created On'])
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'show' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  #[\Override]
  protected function configureShowFields(ShowMapper $show): void
  {
    $show
      ->add('name')
      ->add('id')
      ->add('description')
      ->add('is_public', null, ['label' => 'Public'])
      ->add('is_enabled', null, ['label' => 'Enabled'])
      ->add('allow_comments', null, ['label' => 'Comments'])
      ->add('auto_hidden', null, ['label' => 'Auto Hidden'])
      ->add('created_on', null, ['label' => 'Created On'])
      ->add('updated_on', null, ['label' => 'Updated On'])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('edit')->remove('export');
  }
}
