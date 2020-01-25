<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;


/**
 * Class StoredUserDataAdmin
 * @package App\Admin
 */
class StoredUserDataAdmin extends AbstractAdmin
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
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('username')
      ->add('email')
      ->add('_action', 'actions', [
        'actions' => [
          'retrieve'   => [
            'template' => 'Admin/CRUD/list__action_retrieve_stored_user_data.html.twig',
          ]
        ],
      ]);
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper->add('username', null, [
      'show_filter' => true,
    ])
      ->add('email');
  }

  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list']);
    $collection->add('retrieve', $this->getRouterIdParameter() . '/retrieveUserData');
  }
}
