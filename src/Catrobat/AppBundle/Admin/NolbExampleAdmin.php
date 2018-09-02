<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class NolbExampleAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminnolb_example_program';
  protected $baseRoutePattern = 'nolb_example_program';

  public function createQuery($context = 'list')
  {
    $query = parent::createQuery($context);
    $query->andWhere(
      $query->expr()->isNotNull($query->getRootAlias() . '.program')
    );

    return $query;
  }

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('program', 'entity', ['class' => 'Catrobat\AppBundle\Entity\Program', 'required' => true], ['admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('active', null, ['required' => false])
      ->add('is_for_female', null, ['required' => false]);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('program.name');
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('program', 'entity', ['class' => 'Catrobat\AppBundle\Entity\Program', 'route' => ['name' => 'show'], 'admin_code' => 'catrowebadmin.block.programs.all'])
      ->add('active', null, ['editable' => true])
      ->add('is_for_female', null, ['editable' => true])
      ->add('downloads_from_female')
      ->add('downloads_from_male')
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }

}
