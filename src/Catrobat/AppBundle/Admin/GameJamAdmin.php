<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class GameJamAdmin extends Admin
{
    protected $baseRouteName = 'admin_gamejam';
    protected $baseRoutePattern = 'gamejam';

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('form_url', null, array('sonata_help' => 'Url to the google form, use %CAT_NAME%, %CAT_ID%, and %CAT_EMAIL% as placeholder'))
            ->add('start')
            ->add('end')
            ->add('sample_programs',null,array('class' => 'Catrobat\AppBundle\Entity\Program'),array('admin_code' => 'catrowebadmin.block.programs.all'))
            ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('form_url')
            ->add('start')
            ->add('end')
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('form_url')
            ->add('start')
            ->add('end')
            ->add('_action', 'actions', array('actions' => array(
                'edit' => array(), 'delete' => array(), 'show' => array()
            )))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        // Here we set the fields of the ShowMapper variable, $showMapper (but this can be called anything)
        $showMapper
        ->add('name')
        ->add('form_url')
        ->add('start')
        ->add('end')
        ->add('sample_programs',null,array('class' => 'Catrobat\AppBundle\Entity\Program', 'admin_code' => 'catrowebadmin.block.programs.all'),array('admin_code' => 'catrowebadmin.block.programs.all'))
        ;
    
    }
}
