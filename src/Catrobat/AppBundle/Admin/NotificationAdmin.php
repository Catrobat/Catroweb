<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


/**
 * Class NotificationAdmin
 * @package Catrobat\AppBundle\Admin
 */
class NotificationAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_uploadnotificationadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'upload_notification';


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('upload', null, ['label' => 'Email bei Upload', 'required' => false])
      ->add('report', null,
        ['label' => 'Email bei Inappropriate Report', 'required' => false])
      ->add('summary', null, ['label' => 'Emails täglich sammeln', 'required' => false]);
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  //
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('user.email')
      ->add('upload', null, ['editable' => true])
      ->add('report', null, ['editable' => true])
      ->add('summary', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
  }
}
