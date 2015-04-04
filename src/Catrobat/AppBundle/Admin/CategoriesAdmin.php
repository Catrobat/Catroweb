<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\StarterCategory;

class CategoriesAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_categoriesadmin';
    protected $baseRoutePattern = 'categories';

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array('label' => 'Name'))
            ->add('alias', 'text', array('label' => 'Alias'))
            ->add('programs', null , array(
                'required' => false,
                'by_reference' => false
            ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'id',
                'admin_code' => 'catrowebadmin.block.programs.all',
            ))
            ->add('order', 'integer', array('label' => 'Order'))

        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('alias')
            ->add('programs','entity',array('admin_code' => 'catrowebadmin.block.programs.all'))
            ->add('order')
        ;
    }
}

