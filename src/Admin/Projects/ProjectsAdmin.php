<?php

namespace App\Admin\Projects;

use App\DB\Entity\User\User;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProjectsAdmin extends AbstractAdmin
{
  use ProjectPreUpdateTrait;

  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_projectsadmin';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'projects';

  /**
   * {@inheritdoc}
   */
  protected $datagridValues = [
    '_sort_by' => 'uploaded_at',
    '_sort_order' => 'DESC',
  ];

  private ParameterBagInterface $parameter_bag;

  private ScreenshotRepository $screenshot_repository;

  /**
   * ProjectsAdmin constructor.
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
   */
  public function getThumbnailImageUrl($object): string
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  /**
   * @param FormMapper $form
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('description')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('flavor')
      ->add('visible', null, ['required' => false])
      ->add('approved', null, ['required' => false])
    ;
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('name')
      ->add('user.username', null, ['label' => 'Username'])
      ->add('uploaded_at', DateTimeRangeFilter::class, ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
      ->add('flavor')
      ->add('approved')
      ->add('visible')
    ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $flavor_options = $this->parameter_bag->get('flavors');

    $choices = [];
    if (is_array($flavor_options)) {
      foreach ($flavor_options as $flavor) {
        $choices[$flavor] = $flavor;
      }
    }
    $list
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->addIdentifier('name', 'string', ['sortable' => false])
      ->add('user')
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
      ->add('private', null, ['editable' => false, 'sortable' => false])
      ->add('approved', null, ['editable' => true, 'sortable' => false])
      ->add('visible', null, ['editable' => true, 'sortable' => false])
      ->add('_action', 'actions', ['actions' => [
        'show' => ['template' => 'Admin/CRUD/list__action_show_program_details.html.twig'],
      ]])
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function configureShowFields(ShowMapper $showMapper): void
  {
    $flavor_options = $this->parameter_bag->get('flavors');

    $choices = [];
    if (is_array($flavor_options)) {
      foreach ($flavor_options as $flavor) {
        $choices[$flavor] = $flavor;
      }
    }

    $showMapper
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->add('user')
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
      ->add('private', null, ['editable' => false, 'sortable' => false])
      ->add('approved', null, ['editable' => true, 'sortable' => false])
      ->add('visible', null, ['editable' => true, 'sortable' => false])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
