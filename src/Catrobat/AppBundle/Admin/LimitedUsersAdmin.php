<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class LimitedUsersAdmin extends AbstractAdmin
{

    protected $baseRouteName = 'admin_limited_users';

    protected $baseRoutePattern = 'limited_users';
    
    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->add('username')
            ->add('email')
            ->add('limited', 'boolean', array(
            'editable' => true
        ));
    }
    
    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('username', null, array(
            'show_filter' => true
        ))
            ->add('email')
            ->add('limited');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array(
            'list',
            'edit'
        ));
    }
}
