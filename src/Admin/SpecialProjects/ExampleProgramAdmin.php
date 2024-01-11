<?php

namespace App\Admin\SpecialProjects;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\ExampleProgram;
use App\DB\EntityRepository\FlavorRepository;
use App\Project\ProjectManager;
use App\Storage\ImageRepository;
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

/**
 * @phpstan-extends AbstractAdmin<ExampleProgram>
 */
class ExampleProgramAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminexample_program';

  protected $baseRoutePattern = 'example_program';

  public function __construct(
    private readonly ImageRepository $example_image_repository,
    private readonly ProjectManager $program_manager,
    private readonly FlavorRepository $flavor_repository
  ) {
  }

  /**
   * @param ExampleProgram $object
   */
  public function getExampleImageUrl($object): string
  {
    return '../../'.$this->example_image_repository->getWebPath($object->getId(), $object->getImageType(), false);
  }

  public function getObjectMetadata($object): MetadataInterface
  {
    /** @var ExampleProgram $example_program */
    $example_program = $object;

    return new Metadata($example_program->getProgram()->getName(), $example_program->getProgram()->getDescription(),
      $this->getExampleImageUrl($example_program));
  }

  public function preUpdate(object $object): void
  {
    /** @var ExampleProgram $example_program */
    $example_program = $object;

    $example_program->old_image_type = $example_program->getImageType();
    $this->checkProgramID($example_program);
  }

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
   * {@inheritdoc}
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    unset($this->getListModes()['mosaic']);

    $list
      ->addIdentifier('id')
      ->add('Example Image', null, [
        'accessor' => fn ($subject): string => $this->getExampleImageUrl($subject),
        'template' => 'Admin/example_image.html.twig',
      ])
      ->add('program', EntityType::class, ['class' => Program::class, 'editable' => false])
      ->add('flavor', 'string')
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active')
      ->add(ListMapper::NAME_ACTIONS, null, [
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
