<?php

declare(strict_types=1);

namespace App\Admin\Users\ConsentLog;

use App\DB\Entity\User\ConsentLog;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<ConsentLog>
 */
class ConsentLogAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_consent_log';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'consent-log';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'created_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('created_at', null, ['label' => 'Date'])
      ->add('user', null, ['associated_property' => 'username', 'label' => 'Child'])
      ->add('action')
      ->add('parent_email', null, ['label' => 'Parent Email'])
      ->add('ip_address', null, ['label' => 'IP'])
    ;
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('action')
      ->add('parent_email')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('edit')->remove('export');
  }
}
