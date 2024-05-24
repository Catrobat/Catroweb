<?php

declare(strict_types=1);

namespace App\Admin\Users\UserDataReport;

use App\DB\Entity\User\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<User>
 */
class UserDataReportAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_userdata';

  protected $baseRoutePattern = 'stored_userdata';

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('username')
      ->add('email')
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'retrieve' => [
            'template' => 'Admin/CRUD/list__action_retrieve_stored_user_data.html.twig',
          ],
        ],
      ])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter->add('username', null, [
      'show_filter' => true,
    ])
      ->add('email')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('retrieve', $this->getRouterIdParameter().'/retrieveUserData');
  }
}
