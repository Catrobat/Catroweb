<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

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
   * @return FormBuilder|FormBuilderInterface
   *
   * Override FormBuilder to disable default validation
   */
  public function getFormBuilder()
  {
    $this->formOptions['data_class'] = $this->getClass();

    $options = $this->formOptions;

    $options['validation_groups'] = ['rudeword'];

    $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

    $this->defineFormBuilder($formBuilder);

    unset($this->listModes['mosaic']);

    return $formBuilder;
  }

  /**
   * @param FormMapper $form
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('word')
    ;
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('word')
    ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id', null, ['sortable' => false])
      ->add('word', null, ['sortable' => true])
      ->add('_action', 'actions', ['actions' => [
        'edit' => [],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('acl');
  }
}
