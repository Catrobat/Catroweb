<?php

namespace App\Admin;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AllProgramsAdmin extends AbstractAdmin
{
  use ProgramsTrait;
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_allprogramsadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'all_programs';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'uploaded_at',
    '_sort_order' => 'DESC',
  ];

  private ParameterBagInterface $parameter_bag;

  private ScreenshotRepository $screenshot_repository;

  /**
   * AllProgramsAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, ScreenshotRepository $screenshot_repository,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct($code, $class, $baseControllerName);

    $this->screenshot_repository = $screenshot_repository;
    $this->parameter_bag = $parameter_bag;
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectMetadata($object): MetadataInterface
  {
    return new Metadata($object->getName(), $object->getDescription(), $this->getThumbnailImageUrl($object));
  }

  /**
   * @param mixed $object
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('description')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('downloads')
      ->add('views')
      ->add('flavor')
      ->add('visible', null, ['required' => false])
      ->add('approved', null, ['required' => false])
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
      ->add('id')
      ->add('name')
      ->add('user.username', null, ['label' => 'Username'])
      ->add('uploaded_at', 'doctrine_orm_datetime_range', ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
      ->add('flavor')
      ->add('approved')
      ->add('visible')
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $flavor_options = $this->parameter_bag->get('themes');

    $choices = [];
    foreach ($flavor_options as $flavor)
    {
      $choices[$flavor] = $flavor;
    }
    $listMapper
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->add('user')
      ->addIdentifier('name', 'string', ['sortable' => false])
      ->add('flavor', 'choice', [
        'editable' => true,
        'sortable' => false,
        'choices' => $choices,
      ])
      ->add('views')
      ->add('downloads')
      ->add('thumbnail', 'string',
        [
          'template' => 'Admin/program_thumbnail_image_list.html.twig',
        ]
      )
      ->add('approved', null, ['editable' => true, 'sortable' => false])
      ->add('visible', null, ['editable' => true, 'sortable' => false])
      ->add('_action', 'actions', ['actions' => [
        'show' => ['template' => 'Admin/CRUD/list__action_show_program_details.html.twig'],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
