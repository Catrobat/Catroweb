<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\Program;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReportedProgramsAdmin extends AbstractAdmin
{
  public function createQuery($context = 'list')
  {
    $query = parent::createQuery();

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
        ChoiceType::class,
        ['choices' => [1 => 'NEW', 2 => 'ACCEPTED', 3 => 'REJECTED'], 'editable' => true])
      ->add('time')
      ->add('note')
      ->add('reportingUser', EntityType::class, ['class' => User::class])
      ->add('program', EntityType::class,
        [
          'class'      => Program::class,
          'admin_code' => 'catrowebadmin.block.programs.all',
          'editable'   => false,
        ])
      ->add('program.visible', 'boolean', ['editable' => true])
      ->add('_action', 'actions', ['actions' => [
        'show'            => ['template' => 'CRUD/list__action_show_reported_program_details.html.twig'],
        'edit'            => [],
        'unreportProgram' => ['template' => 'CRUD/list__action_unreportProgram.html.twig'],
      ]]);
  }

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('state',
        ChoiceType::class,
        ['choices' => [1 => 'NEW', 2 => 'ACCEPTED', 3 => 'REJECTED']])
      ->add('program.visible', ChoiceType::class, [
        'choices'  => [
          '0' => 'No',
          '1' => 'Yes',
        ],
        'required' => true,]);
  }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->add('unreportProgram');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
