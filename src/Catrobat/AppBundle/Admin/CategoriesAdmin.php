<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;

class CategoriesAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_adminbundle_categoriesadmin';
    protected $baseRoutePattern = 'categories';

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('alias', 'text', array('label' => 'Alias'))
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
            ->addIdentifier('id')
            ->add('alias')
            ->add('order')
        ;
    }
}

