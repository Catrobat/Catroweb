<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\Program;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportedProgramsAdmin extends AbstractAdmin
{
  public function createQuery($context = 'list')
  {
    $query = parent::createQuery($context);

    return $query;
  }

//  TODO: Log who accepted/rejected
//  public function preUpdate($program)
//  {
//    $old_program = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($program);
//
//    if($old_program["approved"] == false && $program->getApproved() == true)
//    {
//      $program->setApprovedByUser($this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser());
//      $this->getModelManager()->update($program);
//    }elseif($old_program["approved"] == true && $program->getApproved() == false)
//    {
//      $program->setApprovedByUser(null);
//      $this->getModelManager()->update($program);
//    }
//  }


  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('reportingUser.username')
      ->add('time')
      ->add('state')
      ->add('program.visible');
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->add('state',
        'choice',
        ['choices' => [1 => 'NEW', 2 => 'ACCEPTED', 3 => 'REJECTED'], 'editable' => true])
      ->add('time')
      ->add('note')
      ->add('reportingUser', 'entity', ['class' => 'Catrobat\AppBundle\Entity\User'])
      ->add('program', 'entity', ['class' => 'Catrobat\AppBundle\Entity\Program', 'admin_code' => 'catrowebadmin.block.programs.all', 'editable' => false])
      ->add('program.visible', 'boolean', ['editable' => true])
      ->add('_action', 'actions', ['actions' => [
        'show' => ['template' => 'CRUD/list__action_show_reported_program_details.html.twig'],
        'edit' => [],
      ]]);
  }

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('state',
        'choice',
        ['choices' => [1 => 'NEW', 2 => 'ACCEPTED', 3 => 'REJECTED']])
      ->add('program.visible', 'choice', [
        'choices'  => [
          '0' => 'No',
          '1' => 'Yes',
        ],
        'required' => true,]);
  }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
