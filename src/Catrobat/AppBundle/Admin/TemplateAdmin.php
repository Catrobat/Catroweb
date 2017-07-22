<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Model\Metadata;

class TemplateAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_templateadmin';
    protected $baseRoutePattern = 'template';

    protected $datagridValues = array(
        '_sort_by' => 'id',
        '_sort_order' => 'DESC',
    );
    
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $isNew = $this->getSubject()->getId() == null;
        $formMapper
            ->add('name', 'text', array('label' => 'Program name'))
            ->add('landscape_program_file', 'file', array('required' => false))
            ->add('portrait_program_file', 'file', array('required' => false))
            ->add('thumbnail', 'file', array('required' => $isNew))
            ->add('active', null, array('required' => false))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }


    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('thumbnail', 'string', array('template' => ':Admin:program_thumbnail_image_list.html.twig'))
            ->add('active', 'boolean', array('editable' => true))
            ->add('_action', 'actions', array('actions' => array(
                'edit' => array(),
                'delete' => array()
            )))

        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('export');
    }

    public function getThumbnailImageUrl($object)
    {
        return '/'.$this->getConfigurationPool()->getContainer()->get('templatescreenshotrepository')->getThumbnailWebPath($object->getId());
    }

}
