<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class GameJamSubmittedProgramsAdmin extends Admin
{
    protected $parentAssociationMapping = 'gamejam';
    
    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('id')
        ->add('user')
        ->add('name')
        ->add('description')
        ->add('accepted', 'boolean', array('editable' => true))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
    }
}
