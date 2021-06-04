<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class EmailUserMessageAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_mail';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'mail';

  protected function configureFormFields(FormMapper $form): void
  {
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }
}
