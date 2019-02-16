<?php

namespace Catrobat\AppBundle\Admin;


use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class EmailUserMessageAdmin
 * @package Catrobat\AppBundle\Admin
 */
class EmailUserMessageAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_mail';
  protected $baseRoutePattern = 'mail';

  protected function configureFormFields(FormMapper $formMapper)
  {

  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
  }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }

}