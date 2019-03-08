<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class GameJamSubmittedProgramsAdmin
 * @package App\Admin
 */
class GameJamSubmittedProgramsAdmin extends AbstractAdmin
{

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('user')
      ->add('name')
      ->add('description')
      ->add('gamejam_submission_date')
      ->add('gamejam_submission_accepted', 'boolean', ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'show'              => ['template' => 'Admin/CRUD/list__action_show_program_details.html.twig'],
          'removeFromGameJam' => [
            'template' => 'Admin/CRUD/list__action_remove_from_gamejam.html.twig',
          ],
        ],
      ]);
  }


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list', 'edit']);
    $collection->add('removeFromGameJam', $this->getRouterIdParameter() . '/removeFromGameJam');
  }
}
