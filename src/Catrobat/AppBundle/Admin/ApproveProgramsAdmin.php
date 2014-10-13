<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Project;
use Sonata\AdminBundle\Route\RouteCollection;

class ApproveProgramsAdmin extends Admin
{

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias() . '.approved', ':approved_filter')
        );
        $query->setParameter('approved_filter', 'true');
        return $query;
    }

    public function preUpdate($program)
    {
        $old_program = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($program);

        if($old_program["approved"] == false && $program->getApproved() == true)
        {
            $program->setApprovedByUser($this->getConfigurationPool()->getContainer()->get('security.context')->getToken()->getUser());
            $this->getModelManager()->update($program);
        }elseif($old_program["approved"] == true && $program->getApproved() == false)
        {
            $program->setApprovedByUser(null);
            $this->getModelManager()->update($program);
        }
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array('label' => 'Program name'))
            ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User'))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('user')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('user')
            ->add('name')
            ->add('description')
            ->add('approved', 'boolean', array('editable' => true))
            ->add('_action', 'actions', array('actions' => array('show' => array(),)))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create')->remove('delete')->remove('edit');
    }
}

