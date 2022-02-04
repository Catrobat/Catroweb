<?php

namespace App\Admin\SpecialProjects;

use App\Entity\ExampleProgram;
use App\Entity\Flavor;
use App\Entity\Program;
use App\Manager\ProgramManager;
use App\Repository\FlavorRepository;
use App\Repository\ImageRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
  public function __construct($code, $class, $baseControllerName, ImageRepository $example_image_repository,
                              ProgramManager $program_manager, FlavorRepository $flavor_repository)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->example_image_repository = $example_image_repository;
    $this->program_manager = $program_manager;
    $this->flavor_repository = $flavor_repository;
  }

  /**
   * @param ExampleProgram $object
   */
  public function getExampleImageUrl($object): string
  {
    return '../../'.$this->example_image_repository->getWebPath($object->getId(), $object->getImageType(), false);
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectMetadata($object): MetadataInterface
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
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->isNotNull($qb->getRootAliases()[0].'.program')
    );

    return $query;
  }

  /**
   * @param FormMapper $form
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    /** @var ExampleProgram $example_project */
    $example_project = $this->getSubject();
    $file_options = [
      'required' => (null === $example_project->getId()),
    ];

    $id_value = '';

    if (null !== $this->getSubject()->getId()) {
      $file_options['help'] = '<img src="../'.$this->getExampleImageUrl($example_project).'">';
      $id_value = $this->getSubject()->getProgram()->getId();
    }

    $form
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
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('program.name')
    ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    unset($this->listModes['mosaic']);

    $list
      ->addIdentifier('id')
      ->add('Example Image', 'string', ['template' => 'Admin/example_image.html.twig'])
      ->add('program', EntityType::class, [
        'class' => Program::class,
        'admin_code' => 'admin.block.projects.overview',
        'editable' => false,
      ])
      ->add('flavor', 'string')
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active')
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

    if (null !== $program) {
      $object->setProgram($program);
    } else {
      throw new NotFoundHttpException(sprintf('Unable to find program with id : %s', $id));
    }
  }

  private function getFlavors(): array
  {
    return $this->flavor_repository->getFlavorsByNames(['arduino', 'embroidery']);
  }
}
