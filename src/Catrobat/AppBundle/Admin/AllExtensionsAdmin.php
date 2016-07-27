<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Model\Metadata;

class AllExtensionsAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_allxtensionsadmin';
    protected $baseRoutePattern = 'all_extensions';

    protected $datagridValues = array(
        '_sort_by' => 'id',
        '_sort_order' => 'ASC',
    );
    
//     Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array('label' => 'Extension name'))
            ->add('prefix', 'text')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('prefix')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('prefix')
            ->add('_action', 'actions', array('actions' => array(
                'edit' => array(),
            )))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete')->remove('export');
    }

}
