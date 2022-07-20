<?php

namespace App\Admin\Survey;

use App\DB\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

class AllSurveysAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_allsurveysadmin';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'all_surveys';

  public function __construct(
    protected EntityManagerInterface $entity_manager
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $survey_repo = $this->entity_manager->getRepository(Survey::class);
    $existing_surveys = $survey_repo->findAll();

    $remaining_choices = Survey::getISO_639_1_Codes();

    /** @var Survey $existing_survey */
    foreach ($existing_surveys as $existing_survey) {
      unset($remaining_choices[$existing_survey->getLanguageCode()]);
    }

    $form
      ->add('language_code', ChoiceFieldMaskType::class, [
        'choices' => array_flip($remaining_choices),
      ])
      ->add('url')
      ->add('active')
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('language_code', null, [
        'label' => 'Language Code',
        'field_type' => SymfonyChoiceType::class,
        'field_options' => ['choices' => array_flip(Survey::getISO_639_1_Codes()),
        ],
      ])
      ->add('url')
      ->add('active')
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('language_code', 'choice', [
        'choices' => Survey::getISO_639_1_Codes(),
      ])
      ->add('url', 'string', [
        'sortable' => false,
        'editable' => true,
      ])
      ->add('active', 'boolean', [
        'sortable' => true,
        'editable' => true,
      ])
      ->add(ListMapper::NAME_ACTIONS, null, [
        'label' => 'Action',
        'actions' => [
          'delete' => [],
        ],
      ])
    ;
  }

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('export')->remove('acl');
  }
}
