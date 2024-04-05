<?php

declare(strict_types=1);

namespace App\Admin\Projects\ApproveProjects;

use App\Admin\Projects\ProjectPreUpdateTrait;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @phpstan-extends AbstractAdmin<Program>
 */
class ApproveProjectsAdmin extends AbstractAdmin
{
  use ProjectPreUpdateTrait;

  protected $baseRouteName = 'admin_approve_programs';

  protected $baseRoutePattern = 'approve';

  private ?ExtractedCatrobatFile $extractedProject = null;

  public function __construct(
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly ProjectManager $project_manager,
    private readonly ExtractedFileRepository $extracted_file_repository,
    protected TokenStorageInterface $security_token_storage,
    protected ParameterBagInterface $parameter_bag
  ) {
  }

  /**
   * @param mixed|Program $object
   */
  public function getThumbnailImageUrl($object): string
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  public function getContainingImageUrls(mixed $object): array
  {
    /*
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $project_manager ProjectManager
     * @var $object Project
     */

    if (null == $this->extractedProject) {
      $this->extractedProject = $this->extracted_file_repository->loadProjectExtractedFile(
        $this->project_manager->find($object->getId())
      );
    }
    if (null == $this->extractedProject) {
      return [];
    }

    $image_paths = $this->extractedProject->getContainingImagePaths();

    return $this->encodeFileNameOfPathsArray($image_paths);
  }

  public function getContainingSoundUrls(mixed $object): array
  {
    /*
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $projectManager ProjectManager
     * @var $object Project
     */

    if (null == $this->extractedProject) {
      $this->extractedProject = $this->extracted_file_repository->loadProjectExtractedFile(
        $this->project_manager->find($object->getId())
      );
    }

    if (null == $this->extractedProject) {
      return [];
    }

    return $this->encodeFileNameOfPathsArray($this->extractedProject->getContainingSoundPaths());
  }

  /**
   * @param mixed|Program $object
   */
  public function getContainingStrings($object): array
  {
    if (null == $this->extractedProject) {
      $this->extractedProject = $this->extracted_file_repository->loadProjectExtractedFile(
        $this->project_manager->find($object->getId())
      );
    }
    if (null == $this->extractedProject) {
      return [];
    }

    return $this->extractedProject->getContainingStrings();
  }

  /**
   * @param mixed|Program $object
   */
  public function getContainingCodeObjects($object): array
  {
    if (null == $this->extractedProject) {
      $this->extractedProject = $this->extracted_file_repository->loadProjectExtractedFile(
        $this->project_manager->find($object->getId())
      );
    }

    if (null == $this->extractedProject || $this->extractedProject->hasScenes()) {
      return [];
    }

    return $this->extractedProject->getContainingCodeObjects();
  }

  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->eq($qb->getRootAliases()[0].'.approved', $qb->expr()->literal(false))
    );

    return $query;
  }

  protected function configureShowFields(ShowMapper $show): void
  {
    // Here we set the fields of the ShowMapper variable, $show (but this can be called anything)
    $show
      /*
       * The default option is to just display the value as text (for boolean this will be 1 or 0)
       */
      ->add('thumbnail', null, [
        'accessor' => fn ($subject): string => $this->getThumbnailImageUrl($subject),
        'template' => 'Admin/project_thumbnail_image.html.twig',
      ])
      ->add('id')
      ->add('Name')
      ->add('Description')
      ->add('version')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('upload_ip')
      ->add('visible', 'boolean')
      ->add('Images', null, [
        'accessor' => fn ($subject): array => $this->getContainingImageUrls($subject),
        'template' => 'Admin/project_containing_image.html.twig',
      ])
      ->add('Sounds', null, [
        'accessor' => fn ($subject): array => $this->getContainingSoundUrls($subject),
        'template' => 'Admin/project_containing_sound.html.twig',
      ])
      ->add('Strings', null, [
        'accessor' => fn ($subject): array => $this->getContainingStrings($subject),
        'template' => 'Admin/project_containing_strings.html.twig',
      ])
      ->add('Objects', null, [
        'accessor' => fn ($subject): array => $this->getContainingCodeObjects($subject),
        'template' => 'Admin/project_containing_code_objects.html.twig',
      ])
      ->add('Actions', null, [
        'accessor' => function ($subject): void {}, // Just some buttons, nothing to "access"!
        'template' => 'Admin/project_approve_action.html.twig',
      ])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('user', EntityType::class, ['class' => User::class])
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
      ->add('id')
      ->add('name')
      ->add('user.username', null, ['label' => 'User'])
      ->add('uploaded_at', DateTimeRangeFilter::class, ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
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
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->add('id', null, ['sortable' => false])
      ->add('user')
      ->addIdentifier('name')
      ->add('visible', 'boolean', ['editable' => true, 'sortable' => false])
      ->add('approved', 'boolean', ['editable' => true, 'sortable' => false])
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['show' => []]])
    ;
  }

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('delete');
    $collection
      ->add('approve', $this->getRouterIdParameter().'/approve')
      ->add('invisible', $this->getRouterIdParameter().'/invisible')
      ->add('skip', $this->getRouterIdParameter().'/skip')
    ;
  }

  private function encodeFileNameOfPathsArray(mixed $paths): array
  {
    $encoded_paths = [];
    foreach ($paths as $path) {
      $pieces = explode('/', (string) $path);
      $filename = array_pop($pieces);
      $pieces[] = rawurlencode($filename);
      $encoded_paths[] = implode('/', $pieces);
    }

    return $encoded_paths;
  }
}
