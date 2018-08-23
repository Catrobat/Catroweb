<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class GameJamSubmittedProgramsAdmin extends AbstractAdmin
{

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('id')
        ->add('user')
        ->add('name')
        ->add('description')
        ->add('gamejam_submission_date')
        ->add('gamejam_submission_accepted', 'boolean', array('editable' => true))
        ->add('_action', 'actions', array(
            'actions' => array(
                'show' => array('template' => 'CRUD/list__action_show_program_details.html.twig'),
                'removeFromGameJam' => array(
                     'template' => 'CRUD/list__action_remove_from_gamejam.html.twig',
                 )
            )
          ))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list','edit'));
        $collection->add('removeFromGameJam', $this->getRouterIdParameter().'/removeFromGameJam');
    }
}
