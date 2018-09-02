<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class GameJamAdmin extends AbstractAdmin
{
  public function getNewInstance()
  {
    $instance = parent::getNewInstance();

    $instance->setStart(new \DateTime());
    $instance->setEnd(new \DateTime());

    return $instance;
  }

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $returnurl = $this->getConfigurationPool()->getContainer()->get('router')->generate('gamejam_form_submission', ["id" => 42], true);
    $returnurl = str_replace('42', '%CAT_ID%', $returnurl);
    $flavor = $this->getFlavorOptions();
    $formMapper
      ->add('name')
      ->add('form_url', null, ['sonata_help' => '
                Url to the google form, use <code>%CAT_NAME%</code>, <code>%CAT_ID%</code>, <code>%CAT_EMAIL%</code>, and <code>%CAT_LANGUAGE%</code> as placeholder<br>
                Make sure this form calls <code>' . $returnurl . '</code> after completion
                ',
      ])
      ->add('hashtag')
      ->add('flavor', 'choice', ['choices' => $flavor])
      ->add('start')
      ->add('end')
      ->add('sample_programs', null, ['class' => 'Catrobat\AppBundle\Entity\Program'], ['admin_code' => 'catrowebadmin.block.programs.all']);
  }

  private function getFlavorOptions()
  {
    $flavors = $this->getConfigurationPool()->getContainer()->getParameter('gamejam');
    $results = [];
    $results['no flavor'] = null;
    $keys = array_keys($flavors);
    for ($i = 0; $i < count($keys); $i++)
    {
      $results[$keys[$i]] = $keys[$i];
    }

    return $results;
  }


  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('name')
      ->add('form_url')
      ->add('start')
      ->add('end');
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('name')
      ->add('form_url', 'html', ['truncate' => ['length' => 50]])
      ->add('hashtag')
      ->add('flavor')
      ->add('start')
      ->add('end')
      ->add('_action', 'actions', ['actions' => [
        'edit'             => [],
        'delete'           => [],
        'show_submissions' => ['template' => 'CRUD/list__action_show_submitted_programs.html.twig'],
      ]]);
  }

  protected function configureShowFields(ShowMapper $showMapper)
  {
    // Here we set the fields of the ShowMapper variable, $showMapper (but this can be called anything)
    $showMapper
      ->add('name')
      ->add('form_url')
      ->add('hashtag')
      ->add('flavor')
      ->add('start')
      ->add('end')
      ->add('sample_programs', null, ['class' => 'Catrobat\AppBundle\Entity\Program', 'admin_code' => 'catrowebadmin.block.programs.all'], ['admin_code' => 'catrowebadmin.block.programs.all']);

  }

}
