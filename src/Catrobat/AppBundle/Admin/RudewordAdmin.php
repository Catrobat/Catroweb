<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;


/**
 * Class RudewordAdmin
 * @package Catrobat\AppBundle\Admin
 */
class RudewordAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'admin_rudeword';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'rudeword';


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('word');
  }



  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('word');
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('word')
      ->add('_action', 'actions', ['actions' => [
        'edit' => [],
      ]]);
  }
}
