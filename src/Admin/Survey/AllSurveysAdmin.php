<?php

namespace App\Admin\Survey;

use App\DB\Entity\Flavor;
use App\DB\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
      ->add('flavor', EntityType::class, [
        'choice_label' => 'name',
        'class' => Flavor::class,
      ])
      ->add('platform', ChoiceFieldMaskType::class, [
        'choices' => Survey::getAvailablePlatforms(),
      ])
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
    $survey_flavors = $this->getAllSurveyFlavors();
    $flavor_filter_names = $this->createFlavorChoicesArray($survey_flavors);
    $filter
      ->add('language_code', null, [
        'label' => 'Language Code',
        'field_type' => SymfonyChoiceType::class,
        'field_options' => ['choices' => array_flip(Survey::getISO_639_1_Codes()),
        ],
      ])
      ->add('url')
      ->add('flavor.id', null, [
        'label' => 'Flavor',
        'field_type' => SymfonyChoiceType::class,
        'field_options' => ['choices' => $flavor_filter_names],
      ])
      ->add('platform', null, [
        'label' => 'Platform',
        'field_type' => SymfonyChoiceType::class,
        'field_options' => ['choices' => array_flip(Survey::getAvailablePlatforms())],
      ])
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
    $all_flavors = $this->getAllFlavors();
    $flavor_choices = $this->createFlavorChoicesArray($all_flavors);

    $list
      ->add('language_code', 'choice', [
        'choices' => Survey::getISO_639_1_Codes(),
        'editable' => true,
      ])
      ->add('url', 'string', [
        'sortable' => false,
        'editable' => true,
      ])
      ->add('flavor', 'choice', [
        'associated_property' => 'name',
        'label' => 'Flavor',
        'sort_field_mapping' => array(
          'fieldName' => 'id'
        ),
        'sort_parent_association_mappings' => array(
          array('fieldName' => 'flavor'),
        ),
        'editable' => true,
        'choices' => array_flip($flavor_choices),
        'class' => Flavor::class
      ])
      ->add('platform', 'choice', [
        'choices' => Survey::getAvailablePlatforms(),
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

  private function createFlavorChoicesArray(array $all_flavors): array
  {
    $flavor_choices = array();
    foreach($all_flavors as $flavor)
    {
      $flavor_name = $flavor->getName();
      $flavor_id = $flavor->getId();
      $flavor_choices[$flavor_name] = $flavor_id;
    }

    return $flavor_choices;
  }

  private function getAllFlavors(): array
  {
    $flavor_repo = $this->entity_manager->getRepository(Flavor::class);
    $flavors = $flavor_repo->getAllFlavors();

    return $flavors;
  }

  private function getAllSurveyFlavors(): array
  {
    $qb = $this->entity_manager->createQueryBuilder(); // $em is your entity manager
    $query = $qb->select("f")
                 ->from(Survey::class, "s")
                 ->from(Flavor::class, "f")
                 ->where("s.flavor = f.id")
                 ->getQuery();

    $survey_flavors = $query->getResult();
    return $survey_flavors;
  }
}
