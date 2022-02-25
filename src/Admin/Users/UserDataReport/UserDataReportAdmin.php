<?php

namespace App\Admin\Users\UserDataReport;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserDataReportAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_userdata';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'stored_userdata';

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('username')
      ->add('email')
      ->add('_action', 'actions', [
        'actions' => [
          'retrieve' => [
            'template' => 'Admin/CRUD/list__action_retrieve_stored_user_data.html.twig',
          ],
        ],
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
    $collection->clearExcept(['list']);
    $collection->add('retrieve', $this->getRouterIdParameter().'/retrieveUserData');
  }
}
