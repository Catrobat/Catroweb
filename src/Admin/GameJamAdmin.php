<?php

namespace App\Admin;

use App\Entity\Program;
use App\Utils\TimeUtils;
use Exception;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class GameJamAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function getNewInstance()
  {
    $instance = parent::getNewInstance();

    $instance->setStart(TimeUtils::getDateTime());
    $instance->setEnd(TimeUtils::getDateTime());

    return $instance;
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $returnurl = $this->getConfigurationPool()->getContainer()->get('router')
      ->generate('gamejam_form_submission', ['id' => 42], true)
    ;
    $returnurl = str_replace('42', '%CAT_ID%', $returnurl);

    $flavor = $this->getFlavorOptions();
    $formMapper
      ->add('name')
      ->add('form_url', null, ['sonata_help' => '
                Url to the google form, use <code>%CAT_NAME%</code>, <code>%CAT_ID%</code>, <code>%CAT_EMAIL%</code>, and <code>%CAT_LANGUAGE%</code> as placeholder<br>
                Make sure this form calls <code>'.$returnurl.'</code> after completion
                ',
      ])
      ->add('hashtag')
      ->add('flavor', ChoiceType::class, ['choices' => $flavor])
      ->add('start')
      ->add('end')
      ->add('sample_programs', null, ['class' => Program::class],
        ['admin_code' => 'catrowebadmin.block.programs.all'])
    ;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
    $datagridMapper
      ->add('name')
      ->add('form_url')
      ->add('start')
      ->add('end')
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
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
        'edit' => [],
        'delete' => [],
        'show_submissions' => ['template' => 'Admin/CRUD/list__action_show_submitted_programs.html.twig'],
      ]])
    ;
  }

  protected function configureShowFields(ShowMapper $showMapper): void
  {
    // Here we set the fields of the ShowMapper variable, $showMapper (but this can be called anything)
    $showMapper
      ->add('name')
      ->add('form_url')
      ->add('hashtag')
      ->add('flavor')
      ->add('start')
      ->add('end')
      ->add('sample_programs', null,
        ['class' => Program::class, 'admin_code' => 'catrowebadmin.block.programs.all'])
    ;
  }

  /**
   * @return array
   */
  private function getFlavorOptions()
  {
    $flavors = $this->getConfigurationPool()->getContainer()->getParameter('gamejam');
    $results = [];
    $results['no flavor'] = null;
    $keys = array_keys($flavors);
    foreach ($keys as $i => $key)
    {
      $results[$keys[$i]] = $key;
    }

    return $results;
  }
}
