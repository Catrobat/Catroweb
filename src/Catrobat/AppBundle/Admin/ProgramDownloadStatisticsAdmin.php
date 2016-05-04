<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ProgramDownloadStatisticsAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_programdownloadstatisticsadmin';
    protected $baseRoutePattern = 'download_stats';

    protected $datagridValues = array(
        '_sort_by' => 'id',
        '_sort_order' => 'DESC'
    );

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('program', 'entity', array('class' => 'Catrobat\AppBundle\Entity\Program'), array(
                'admin_code' => 'catrowebadmin.block.programs.all'))
            ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User'))
            ->add('downloaded_at')
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
            ->add('program.name')
            ->add('user.username')
            ->add('program.gamejam_submission_accepted')
            ->add('downloaded_at')
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
            ->add('program', null, array('admin_code' => 'catrowebadmin.block.programs.all',))
            ->add('user')
            ->add('downloaded_at')
            ->add('ip')
            ->add('latitude')
            ->add('longitude')
            ->add('country_code')
            ->add('country_name')
            ->add('street')
            ->add('postal_code')
            ->add('locality')
            ->add('program.downloads')
            ->add('program.apk_downloads')
            ->add('user_agent')
            ->add('referrer')
            ->add('_action', 'actions', array('actions' => array(
                'edit' => array()
            )))
        ;
    }

    public function getExportFields() {
        return array('id','program.id','program.name','program.gamejam_submission_accepted','program.downloads','program.apk_downloads','program.description','downloaded_at','ip','latitude','longitude','country_code',
            'country_name','street','postal_code','locality','user_agent','user.username','referrer');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create')->remove('delete');
    }
}
