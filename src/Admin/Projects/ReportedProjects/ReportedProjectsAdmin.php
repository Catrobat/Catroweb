<?php

declare(strict_types=1);

namespace App\Admin\Projects\ReportedProjects;

use App\DB\Entity\Project\Program;
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
 * @phpstan-extends AbstractAdmin<Program>
 */
class ReportedProjectsAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_reported_projects';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'project/report';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('time', DateTimeRangeFilter::class,
        [
          'field_type' => DateTimeRangePickerType::class,
        ])
      ->add('state', NumberFilter::class,
        [
          'field_type' => SymfonyChoiceType::class,
          'field_options' => ['choices' => ['New' => 1, 'Accepted' => 2, 'Rejected' => 3]],
        ])
      ->add('category')
      ->add('reportingUser.username')
      ->add('program.visible')
      ->add('reportedUser')
    ;
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
      ->add('time')
      ->add('state', 'choice',
        [
          'choices' => [1 => 'New', 2 => 'Accepted', 3 => 'Rejected'],
        ])
      ->add('category', null, ['sortable' => false])
      ->add('note', null, ['sortable' => false])
      ->add('reportingUser', EntityType::class, ['class' => User::class])
      ->add('reportedUser')
      ->add('program', EntityType::class,
        [
          'class' => Program::class,
          'editable' => false,
        ])
      ->add('program.visible')
      ->add('program.approved', null, ['sortable' => false])
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'unreportProject' => ['template' => 'Admin/CRUD/list__action_unreportProject.html.twig'],
        'acceptProjectReport' => ['template' => 'Admin/CRUD/list__action_accept_project_report.html.twig'],
      ]])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->add('unreportProject');
    $collection->add('acceptProjectReport');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
