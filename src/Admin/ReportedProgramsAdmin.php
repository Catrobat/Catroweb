<?php

namespace App\Admin;

use App\Entity\Program;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

class ReportedProgramsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_reported_projects';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'reported_projects';

  /**
   * @var array
   */
  protected $datagridValues = ['_sort_order' => 'DESC'];

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('time', 'doctrine_orm_datetime_range',
        [
          'field_type' => DateTimeRangePickerType::class,
        ])
      ->add('state', 'doctrine_orm_string',
        [
          'field_type' => SymfonyChoiceType::class,
          'field_options' => ['choices' => ['New' => '1', 'Accepted' => '2', 'Rejected' => '3']],
        ])
      ->add('category', 'doctrine_orm_string',
        [
          'field_type' => SymfonyChoiceType::class,
          'field_options' => ['choices' => ['Dislike' => 'Dislike', 'Spam' => 'Spam',
            'Copyright Infringement' => 'Copyright Infringement', 'Inappropriate' => 'Inappropriate', ]],
        ])
      ->add('reportingUser.username')
      ->add('program.visible')
      ->add('reportedUser')
    ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
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
          'admin_code' => 'catrowebadmin.block.programs.all',
          'editable' => false,
        ])
      ->add('program.visible')
      ->add('program.approved', null, ['sortable' => false])
      ->add('_action', 'actions', ['actions' => [
        'unreportProgram' => ['template' => 'Admin/CRUD/list__action_unreportProgram.html.twig'],
        'acceptProgramReport' => ['template' => 'Admin/CRUD/list__action_accept_program_report.html.twig'],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->add('unreportProgram');
    $collection->add('acceptProgramReport');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
