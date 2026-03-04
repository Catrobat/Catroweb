<?php

declare(strict_types=1);

namespace App\Admin\Moderation;

use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\User\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

/**
 * @phpstan-extends AbstractAdmin<ContentAppeal>
 */
class AppealQueueAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_moderation_appeals';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'moderation/appeal';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'created_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('content_type', null, ['label' => 'Content Type'])
      ->add('state', NumberFilter::class, [
        'field_type' => SymfonyChoiceType::class,
        'field_options' => ['choices' => ['Pending' => 1, 'Approved' => 2, 'Rejected' => 3]],
      ])
      ->add('created_at', DateTimeRangeFilter::class, [
        'field_type' => DateTimeRangePickerType::class,
      ])
      ->add('appellant', null, ['label' => 'Appellant'])
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('created_at', null, ['label' => 'Date'])
      ->add('content_type', null, ['label' => 'Type'])
      ->add('content_id', null, [
        'label' => 'Content',
        'template' => 'Admin/CRUD/list__field_content_link.html.twig',
      ])
      ->add('appellant', EntityType::class, ['class' => User::class, 'label' => 'Appellant'])
      ->add('reason', null, ['sortable' => false])
      ->add('state', 'choice', [
        'choices' => [1 => 'Pending', 2 => 'Approved', 3 => 'Rejected'],
      ])
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'approveAppeal' => ['template' => 'Admin/CRUD/list__action_approve_appeal.html.twig'],
        'rejectAppeal' => ['template' => 'Admin/CRUD/list__action_reject_appeal.html.twig'],
      ]])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->add('approveAppeal', $this->getRouterIdParameter().'/approveAppeal');
    $collection->add('rejectAppeal', $this->getRouterIdParameter().'/rejectAppeal');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
