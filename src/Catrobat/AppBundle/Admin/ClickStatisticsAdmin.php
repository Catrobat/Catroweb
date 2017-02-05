<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ClickStatisticsAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_clickstatisticsadmin';
    protected $baseRoutePattern = 'click_stats';

    protected $datagridValues = array(
        '_sort_by' => 'id',
        '_sort_order' => 'DESC'
    );

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('type')
            ->add('program', 'entity', array('class' => 'Catrobat\AppBundle\Entity\Program'), array(
                'admin_code' => 'catrowebadmin.block.programs.all'))
            ->add('scratch_program_id')
            ->add('recommended_from_program', 'entity', array('class' => 'Catrobat\AppBundle\Entity\Program'), array('admin_code' => 'catrowebadmin.block.programs.all'))
            ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User'))
            ->add('clicked_at')
            ->add('ip')
            ->add('latitude')
            ->add('longitude')
            ->add('country_code')
            ->add('country_name')
            ->add('street')
            ->add('postal_code')
            ->add('locality')
            ->add('user_agent')
            ->add('referrer');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('type')
            ->add('program.name')
            ->add('scratch_program_id')
            ->add('recommended_from_program.name')
            ->add('user.username')
            ->add('ip')
            ->add('country_name')
            ->add('user_agent')
            ->add('referrer')
            ->add('locality');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('type')
            ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User'))
            ->add('program', 'entity', array('admin_code' => 'catrowebadmin.block.programs.all'))
            ->add('scratch_program_id')
            ->add('recommended_from_program', 'entity', array('admin_code' => 'catrowebadmin.block.programs.all'))
            ->add('tag.en', null, array(
                'label' => 'Tag'))
            ->add('extension.name', null, array(
                'label' => 'Extension'))
            ->add('clicked_at')
            ->add('ip')
            ->add('latitude')
            ->add('longitude')
            ->add('country_code')
            ->add('country_name')
            ->add('street')
            ->add('postal_code')
            ->add('locality')
            ->add('user_agent')
            ->add('referrer')
            ->add('_action', 'actions', array('actions' => array(
                'edit' => array()
            )))
        ;
    }

    public function getExportFields() {
        return array('id','user.username','program.id','program.name','scratch_program_id','recommended_from_program.id',
            'recommended_from_program.name','tag.en','extension.name','clicked_at','ip','latitude','longitude','country_code',
            'country_name','street','postal_code','locality','user_agent','referrer');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create')->remove('delete');
    }
}
