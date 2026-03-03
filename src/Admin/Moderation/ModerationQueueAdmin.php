<?php

declare(strict_types=1);

namespace App\Admin\Moderation;

use App\DB\Entity\Moderation\ContentReport;
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
 * @phpstan-extends AbstractAdmin<ContentReport>
 */
class ModerationQueueAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_moderation_reports';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'moderation/report';
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
        'field_options' => ['choices' => ['New' => 1, 'Accepted' => 2, 'Rejected' => 3]],
      ])
      ->add('category')
      ->add('created_at', DateTimeRangeFilter::class, [
        'field_type' => DateTimeRangePickerType::class,
      ])
      ->add('reporter', null, ['label' => 'Reporter'])
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
      ->add('category')
      ->add('note', null, ['sortable' => false])
      ->add('reporter', EntityType::class, ['class' => User::class, 'label' => 'Reporter'])
      ->add('reporter_trust_score', null, ['label' => 'Trust Score'])
      ->add('state', 'choice', [
        'choices' => [1 => 'New', 2 => 'Accepted', 3 => 'Rejected'],
      ])
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'acceptReport' => ['template' => 'Admin/CRUD/list__action_accept_report.html.twig'],
        'rejectReport' => ['template' => 'Admin/CRUD/list__action_reject_report.html.twig'],
      ]])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->add('acceptReport', $this->getRouterIdParameter().'/acceptReport');
    $collection->add('rejectReport', $this->getRouterIdParameter().'/rejectReport');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
