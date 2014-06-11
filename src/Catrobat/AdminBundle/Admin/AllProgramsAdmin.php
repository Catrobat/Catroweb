<?php
namespace Catrobat\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\CoreBundle\Entity\User;

class AllProgramsAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_allprogramsadmin';
    protected $baseRoutePattern = 'all_programs';

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array('label' => 'Program name'))
            ->add('user', 'entity', array('class' => 'Catrobat\CoreBundle\Entity\User'))
            ->add('approved', null, array('required' => false))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('downloads')
            ->add('user')
        ;
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

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('user')
            ->add('name')
            ->add('description')
            ->add('views')
            ->add('downloads')
            ->add('thumbnail')
            ->add('approved')
            ->add('approved_by_user')
        ;
    }
}

