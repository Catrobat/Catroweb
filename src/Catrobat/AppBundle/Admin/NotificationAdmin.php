<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;

class NotificationAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_uploadnotificationadmin';
  protected $baseRoutePattern = 'upload_notification';

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('user', 'entity', ['class' => 'Catrobat\AppBundle\Entity\User'])
      ->add('upload', null, ['label' => 'Email bei Upload', 'required' => false])
      ->add('report', null, ['label' => 'Email bei Inappropriate Report', 'required' => false])
      ->add('summary', null, ['label' => 'Emails täglich sammeln', 'required' => false]);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper;
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->add('user', 'entity', ['class' => 'Catrobat\AppBundle\Entity\User'])
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

  protected function configureRoutes(RouteCollection $collection)
  {
  }
}
