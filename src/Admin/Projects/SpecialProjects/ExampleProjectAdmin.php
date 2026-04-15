<?php

declare(strict_types=1);

namespace App\Admin\Projects\SpecialProjects;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Special\ExampleProject;
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
 * @phpstan-extends AbstractAdmin<ExampleProject>
 */
class ExampleProjectAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_example_project';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'project/example';
  }

  public function __construct(
    private readonly ImageRepository $example_image_repository,
    private readonly ProjectManager $project_manager,
    private readonly FlavorRepository $flavor_repository,
  ) {
  }

  public function getExampleImageUrl(ExampleProject $object): string
  {
    $id = $object->getId();
    if (null === $id) {
      return '';
    }

    return '../../'.$this->example_image_repository->getWebPath($id, $object->getImageType(), false);
  }

  #[\Override]
  public function getObjectMetadata($object): MetadataInterface
  {
    /** @var ExampleProject $example_project */
    $example_project = $object;
    $project = $example_project->getProject();

    return new Metadata(
      $project?->getName() ?? '',
      $project?->getDescription() ?? '',
      $this->getExampleImageUrl($example_project),
    );
  }

  #[\Override]
  protected function preUpdate(object $object): void
  {
    /** @var ExampleProject $example_project */
    $example_project = $object;

    $example_project->old_image_type = $example_project->getImageType();
    $this->checkProjectID($example_project);
  }

  #[\Override]
  protected function prePersist($object): void
  {
    $this->checkProjectID($object);
  }

  #[\Override]
  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->isNotNull($qb->getRootAliases()[0].'.project')
    );

    /* @psalm-suppress LessSpecificReturnStatement, MoreSpecificReturnType */
    return $query; // @phpstan-ignore return.type
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    /** @var ExampleProject $example_project */
    $example_project = $this->getSubject();
    $file_options = [
      'required' => (null === $example_project->getId()),
    ];

    $id_value = '';

    if (null !== $this->getSubject()->getId()) {
      $file_options['help'] = '<img src="../'.$this->getExampleImageUrl($example_project).'">';
      $id_value = $this->getSubject()->getProject()->getId();
    }

    $form
      ->add('file', FileType::class, $file_options)
      ->add('program_id', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor', null, ['class' => Flavor::class, 'choices' => $this->getFlavors(), 'required' => true])
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
        'help' => 'Toggle for iOS example projects api call.', ])
      ->add('active', null, ['required' => false])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
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
  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    unset($this->getListModes()['mosaic']);

    $list
      ->addIdentifier('id')
      ->add('Example Image', null, [
        'accessor' => $this->getExampleImageUrl(...),
        'template' => 'Admin/Projects/ExampleImage.html.twig',
      ])
      ->add('project', EntityType::class, ['class' => Project::class, 'editable' => false])
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

  private function checkProjectID(ExampleProject $object): void
  {
    $id = $this->getForm()->get('program_id')->getData();
    $project = $this->project_manager->find($id);

    if ($project instanceof Project) {
      $object->setProject($project);
    } else {
      throw new NotFoundHttpException(sprintf('Unable to find project with id : %s', $id));
    }
  }

  private function getFlavors(): array
  {
    return $this->flavor_repository->getFlavorsByNames([Flavor::ARDUINO, Flavor::EMBROIDERY]);
  }
}
