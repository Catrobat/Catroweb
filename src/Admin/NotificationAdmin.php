<?php

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class NotificationAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_subscriptonsadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'subscriptions';

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    if ($this->isCurrentRoute('create'))
    {
      $formMapper
        ->add('id', EntityType::class, ['class' => User::class, 'label' => 'User'])
      ;
    }

    if ($this->isCurrentRoute('edit'))
    {
      $formMapper
        ->add('user', EntityType::class, ['class' => User::class, 'label' => 'User'])
      ;
    }

    $formMapper
      ->add('upload', null, ['label' => 'Email bei Upload', 'required' => false])
      ->add('report', null,
        ['label' => 'Email bei Inappropriate Report', 'required' => false])
    ;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
    $datagridMapper
      ->add('user')
      ->add('user.email')
      ->add('upload')
      ->add('report')
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $listMapper
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('user.email')
      ->add('upload', null, ['editable' => true])
      ->add('report', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('acl');
  }
}
