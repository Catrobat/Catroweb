<?php

namespace App\Admin\Users\ReportedUsers;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportedUsersAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_reported_users';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'reported_users';

  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('username')
      ->add('email')
      ->add('_action', 'actions', ['actions' => [
        'createUrlComments' => ['template' => 'Admin/CRUD/list__action_create_url_comments.html.twig'],
        'createUrlPrograms' => ['template' => 'Admin/CRUD/list__action_create_url_programs.html.twig'],
      ]])
      ->add(
          'getReportedCommentsCount',
          null,
          [
            'label' => '#Reported Comments',
            'sortable' => true,
            'sort_field_mapping' => ['fieldName' => 'id'],
            'sort_parent_association_mappings' => [],
          ])
      ->add(
          'getProgramInappropriateReportsCount',
          null,
          [
            'label' => '#Reported Programs',
            'sortable' => true,
            'sort_field_mapping' => ['fieldName' => 'id'],
            'sort_parent_association_mappings' => [],
          ])
    ;
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter->add('username', null, [
      'show_filter' => true,
    ])
      ->add('email')
      ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete');
    $collection->add('createUrlComments');
    $collection->add('createUrlPrograms');
  }
}
