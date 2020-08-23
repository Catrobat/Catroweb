<?php

namespace App\Admin;

use App\Catrobat\Services\ImageRepository;
use App\Entity\ExampleProgram;
use App\Entity\Flavor;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Repository\FlavorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExampleProgramAdmin extends AbstractAdmin
{
  /**
   * @override
   *
   * @var string
   */
  protected $baseRouteName = 'adminexample_program';

  /**
   * @override
   *
   * @var string
   */
  protected $baseRoutePattern = 'example_program';

  private EntityManagerInterface $entity_manager;

  private ParameterBagInterface $parameter_bag;

  private ImageRepository $example_image_repository;

  private ProgramManager $program_manager;

  private FlavorRepository $flavor_repository;

  /**
   * ExampleProgramAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, EntityManagerInterface $entity_manager,
                              ParameterBagInterface $parameter_bag, ImageRepository $example_image_repository,
                              ProgramManager $program_manager, FlavorRepository $flavor_repository)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->entity_manager = $entity_manager;
    $this->parameter_bag = $parameter_bag;
    $this->example_image_repository = $example_image_repository;
    $this->program_manager = $program_manager;
    $this->flavor_repository = $flavor_repository;
  }

  /**
   * @param ExampleProgram $object
   *
   * @return string
   */
  public function getExampleImageUrl($object)
  {
    return '../../'.$this->example_image_repository->getWebPath($object->getId(), $object->getImageType(), false);
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectMetadata($object): Metadata
  {
    /** @var ExampleProgram $example_program */
    $example_program = $object;

    return new Metadata($example_program->getProgram()->getName(), $example_program->getProgram()->getDescription(),
      $this->getExampleImageUrl($example_program));
  }

  /**
   * {@inheritdoc}
   */
  public function preUpdate($object): void
  {
    /** @var ExampleProgram $example_program */
    $example_program = $object;

    $example_program->old_image_type = $example_program->getImageType();
    $this->checkProgramID($example_program);
  }

  /**
   * @param mixed $object
   */
  public function prePersist($object): void
  {
    $this->checkProgramID($object);
  }

  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    $query = parent::configureQuery($query);

    if (!$query instanceof \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery)
    {
      return $query;
    }

    /** @var QueryBuilder $qb */
    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->isNotNull($qb->getRootAliases()[0].'.program')
    );

    return $query;
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    /** @var ExampleProgram $example_project */
    $example_project = $this->getSubject();
    $file_options = [
      'required' => (null === $example_project->getId()),
    ];

    $id_value = '';

    if (null !== $this->getSubject()->getId())
    {
      $file_options['help'] = '<img src="../'.$this->getExampleImageUrl($example_project).'">';
      $id_value = $this->getSubject()->getProgram()->getId();
    }

    $formMapper
      ->add('file', FileType::class, $file_options)
      ->add('program_id', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor', null, ['class' => Flavor::class, 'choices' => $this->getFlavors(), 'required' => true])
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
        'help' => 'Toggle for iOS example programs api call.', ])
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
      ->addIdentifier('id')
      ->add('Example Image', 'string', ['template' => 'Admin/example_image.html.twig'])
      ->add('program', EntityType::class, [
        'class' => Program::class,
        'admin_code' => 'catrowebadmin.block.programs.all',
        'editable' => false,
      ])
      ->add('flavor', 'string')
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

  /**
   * @param ExampleProgram $object
   */
  private function checkProgramID($object): void
  {
    $id = $this->getForm()->get('program_id')->getData();

    $program = $this->program_manager->find($id);

    if (null !== $program)
    {
      $object->setProgram($program);
    }
    else
    {
      throw new NotFoundHttpException(sprintf('Unable to find program with id : %s', $id));
    }
  }

  private function getFlavors(): array
  {
    return $this->flavor_repository->getFlavorsByNames(['arduino', 'embroidery']);
  }
}
