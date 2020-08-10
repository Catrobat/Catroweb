<?php

namespace App\Admin;

use App\Admin\Forms\FeaturedImageConstraint;
use App\Catrobat\Services\ImageRepository;
use App\Entity\FeaturedProgram;
use App\Entity\Flavor;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class FeaturedProgramAdmin extends AbstractAdmin
{
  /**
   * @override
   *
   * @var string
   */
  protected $baseRouteName = 'adminfeatured_program';

  /**
   * @override
   *
   * @var string
   */
  protected $baseRoutePattern = 'featured_program';

  private EntityManagerInterface $entity_manager;

  private ParameterBagInterface $parameter_bag;

  private ImageRepository $featured_image_repository;

  private ProgramManager $program_manager;

  /**
   * FeaturedProgramAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, EntityManagerInterface $entity_manager,
                              ParameterBagInterface $parameter_bag, ImageRepository $featured_image_repository,
                              ProgramManager $program_manager)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->entity_manager = $entity_manager;
    $this->parameter_bag = $parameter_bag;
    $this->featured_image_repository = $featured_image_repository;
    $this->program_manager = $program_manager;
  }

  /**
   * @param FeaturedProgram $object
   *
   * @return string
   */
  public function getFeaturedImageUrl($object)
  {
    return '../../'.$this->featured_image_repository->getWebPath($object->getId(), $object->getImageType(), true);
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectMetadata($object): Metadata
  {
    /** @var FeaturedProgram $featured_program */
    $featured_program = $object;

    return new Metadata($featured_program->getProgram()->getName(), $featured_program->getProgram()->getDescription(),
      $this->getFeaturedImageUrl($featured_program));
  }

  /**
   * {@inheritdoc}
   */
  public function preUpdate($object): void
  {
    /** @var FeaturedProgram $featured_program */
    $featured_program = $object;

    $featured_program->old_image_type = $featured_program->getImageType();
  }

  public function validate(ErrorElement $errorElement, $object): void
  {
    $id = $this->getForm()->get('Program_Id_or_Url')->getData();

    if ($this->getForm()->get('Use_Url')->getData())
    {
      if (filter_var($id, FILTER_VALIDATE_URL))
      {
        $object->setUrl($id);
        if (null !== $object->getId())
        {
          $object->setProgram(null);
        }
      }
      else
      {
        $errorElement->with('ID')->addViolation('Please enter a valid URL.')->end();
      }
    }
    else
    {
      if (null !== $id)
      {
        $id = preg_replace('$(.*)/project/$', '', $id);
      }

      $program = $this->program_manager->find($id);

      if (null !== $program)
      {
        $object->setProgram($program);
        if (null !== $object->getURL())
        {
          $object->setURL(null);
        }
      }
      else
      {
        $errorElement->with('ID')->addViolation('Unable to find program with given ID.')->end();
      }
    }
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    /** @var FeaturedProgram $featured_project */
    $featured_project = $this->getSubject();
    $file_options = [
      'required' => (null === $featured_project->getId()),
      'constraints' => [
        new FeaturedImageConstraint(),
      ],
    ];

    $id_value = '';
    $use_url = false;

    if (null !== $this->getSubject()->getId())
    {
      $file_options['help'] = '<img src="../'.$this->getFeaturedImageUrl($featured_project).'">';

      $id_value = $this->getSubject()->getUrl();
      $use_url = true;
      if (null == $id_value)
      {
        $id_value = $this->getSubject()->getProgram()->getId();
        $use_url = false;
      }
    }
    $formMapper
      ->add('file', FileType::class, $file_options,
        ['help' => 'The featured image must be of size 1024 x 400'])
      ->add('Use_Url', CheckboxType::class, ['mapped' => false, 'required' => false,
        'help' => 'Toggle to save URL instead of Program ID.', 'data' => $use_url, ])
      ->add('Program_Id_or_Url', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor', null, ['class' => Flavor::class, 'multiple' => false, 'required' => true])
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
        'help' => 'Toggle for iOS featured programs api call.', ])
      ->add('active', null, ['required' => false])
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
      ->add('program.name')
      ->add('for_ios')
      ->add('active')
      ->add('priority')
      ->add('flavor')
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    unset($this->listModes['mosaic']);
    $listMapper
      ->addIdentifier('id', null, [
        'sortable' => false,
      ])
      ->add('Featured Image', 'string', ['template' => 'Admin/featured_image.html.twig'])
      ->add('program', EntityType::class, [
        'class' => Program::class,
        'admin_code' => 'catrowebadmin.block.programs.all',
        'editable' => false,
      ])
      ->add('url', UrlType::class)
      ->add('flavor', 'string', [
        'sortable' => false,
      ])
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active', null)
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('acl');
  }
}
