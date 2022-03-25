<?php

namespace App\Admin\Projects\ApproveProjects;

use App\Admin\Projects\ProjectPreUpdateTrait;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProgramManager;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ApproveProjectsAdmin extends AbstractAdmin
{
  use ProjectPreUpdateTrait;

  /**
   * @var string
   */
  protected $baseRouteName = 'admin_approve_programs';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'approve';

  private ?ExtractedCatrobatFile $extractedProgram = null;

  private ScreenshotRepository $screenshot_repository;

  private ProgramManager $program_manager;

  private ExtractedFileRepository $extracted_file_repository;

  /**
   * ApproveProjectsAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, ScreenshotRepository $screenshot_repository,
                              ProgramManager $program_manager, ExtractedFileRepository $extracted_file_repository)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->screenshot_repository = $screenshot_repository;
    $this->program_manager = $program_manager;
    $this->extracted_file_repository = $extracted_file_repository;
  }

  /**
   * @param mixed|Program $object
   */
  public function getThumbnailImageUrl($object): string
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  /**
   * @param mixed $object
   */
  public function getContainingImageUrls($object): array
  {
    /*
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $program_manager ProgramManager
     * @var $object Program
     */

    if (null == $this->extractedProgram) {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }
    if (null == $this->extractedProgram) {
      return [];
    }

    $image_paths = $this->extractedProgram->getContainingImagePaths();

    return $this->encodeFileNameOfPathsArray($image_paths);
  }

  /**
   * @param mixed $object
   */
  public function getContainingSoundUrls($object): array
  {
    /*
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $progManager ProgramManager
     * @var $object Program
     */

    if (null == $this->extractedProgram) {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }

    if (null == $this->extractedProgram) {
      return [];
    }

    return $this->encodeFileNameOfPathsArray($this->extractedProgram->getContainingSoundPaths());
  }

  /**
   * @param mixed|Program $object
   */
  public function getContainingStrings($object): array
  {
    if (null == $this->extractedProgram) {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }
    if (null == $this->extractedProgram) {
      return [];
    }

    return $this->extractedProgram->getContainingStrings();
  }

  /**
   * @param mixed|Program $object
   */
  public function getContainingCodeObjects($object): array
  {
    if (null == $this->extractedProgram) {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }

    if (null == $this->extractedProgram || $this->extractedProgram->hasScenes()) {
      return [];
    }

    return $this->extractedProgram->getContainingCodeObjects();
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
      ->add('Thumbnail', null, ['template' => 'Admin/program_thumbnail_image.html.twig'])
      ->add('id')
      ->add('Name')
      ->add('Description')
      ->add('version')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('upload_ip')
      ->add('visible', 'boolean')
      ->add('Images', null, ['template' => 'Admin/program_containing_image.html.twig'])
      ->add('Sounds', null, ['template' => 'Admin/program_containing_sound.html.twig'])
      ->add('Strings', null, ['template' => 'Admin/program_containing_strings.html.twig'])
      ->add('Objects', null, ['template' => 'Admin/program_containing_code_objects.html.twig'])
      ->add('', null, ['template' => 'Admin/program_approve_action.html.twig'])
    ;
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
      ->add('user', EntityType::class, ['class' => User::class])
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
      ->add('user.username', null, ['label' => 'User'])
      ->add('uploaded_at', DateTimeRangeFilter::class, ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
    ;
  }

  /**
   * @param ListMapper $list
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
      ->add('_action', 'actions', ['actions' => ['show' => []]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete');
    $collection
      ->add('approve', $this->getRouterIdParameter().'/approve')
      ->add('invisible', $this->getRouterIdParameter().'/invisible')
      ->add('skip', $this->getRouterIdParameter().'/skip')
    ;
  }

  /**
   * @param mixed $paths
   */
  private function encodeFileNameOfPathsArray($paths): array
  {
    $encoded_paths = [];
    foreach ($paths as $path) {
      $pieces = explode('/', $path);
      $filename = array_pop($pieces);
      $pieces[] = rawurlencode($filename);
      $encoded_paths[] = implode('/', $pieces);
    }

    return $encoded_paths;
  }
}
