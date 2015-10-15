<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

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
                'edit' => array(), 'delete' => array(),
            )))
        ;
    }
}
