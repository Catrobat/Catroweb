<?php

namespace App\Admin;

use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ApproveProgramsAdmin extends AbstractAdmin
{
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
   * ApproveProgramsAdmin constructor.
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
   * @param mixed|Program $program
   *
   * @throws \Sonata\AdminBundle\Exception\ModelManagerException
   */
  public function preUpdate($program): void
  {
    /** @var ModelManager $model_manager */
    $model_manager = $this->getModelManager();
    $old_program = $model_manager->getEntityManager($this->getClass())->getUnitOfWork()
      ->getOriginalEntityData($program)
    ;

    if (false == $old_program['approved'] && true == $program->getApproved())
    {
      /** @var User $user */
      $user = $this->getConfigurationPool()->getContainer()
        ->get('security.token_storage')->getToken()->getUser();
      $program->setApprovedByUser($user);
      $this->getModelManager()->update($program);
    }
    elseif (true == $old_program['approved'] && false == $program->getApproved())
    {
      $program->setApprovedByUser(null);
      $this->getModelManager()->update($program);
    }
  }

  /**
   * @param mixed|Program $object
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  /**
   * @param mixed $object
   *
   * @return array
   */
  public function getContainingImageUrls($object)
  {
    /*
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $program_manager ProgramManager
     * @var $object Program
     */

    if (null == $this->extractedProgram)
    {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }
    if (null == $this->extractedProgram)
    {
      return [];
    }

    $image_paths = $this->extractedProgram->getContainingImagePaths();

    return $this->encodeFileNameOfPathsArray($image_paths);
  }

  /**
   * @param mixed $object
   *
   * @return array
   */
  public function getContainingSoundUrls($object)
  {
    /*
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $progManager ProgramManager
     * @var $object Program
     */

    if (null == $this->extractedProgram)
    {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }

    if (null == $this->extractedProgram)
    {
      return [];
    }

    return $this->encodeFileNameOfPathsArray($this->extractedProgram->getContainingSoundPaths());
  }

  /**
   * @param mixed|Program $object
   *
   * @return array
   */
  public function getContainingStrings($object)
  {
    if (null == $this->extractedProgram)
    {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }
    if (null == $this->extractedProgram)
    {
      return [];
    }

    return $this->extractedProgram->getContainingStrings();
  }

  /**
   * @param mixed|Program $object
   *
   * @return array
   */
  public function getContainingCodeObjects($object)
  {
    if (null == $this->extractedProgram)
    {
      $this->extractedProgram = $this->extracted_file_repository->loadProgramExtractedFile(
        $this->program_manager->find($object->getId())
      );
    }

    if (null == $this->extractedProgram || $this->extractedProgram->hasScenes())
    {
      return [];
    }

    return $this->extractedProgram->getContainingCodeObjects();
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
      $qb->expr()->eq($qb->getRootAliases()[0].'.approved', $qb->expr()->literal(false))
    );

    return $query;
  }

  protected function configureShowFields(ShowMapper $showMapper): void
  {
    // Here we set the fields of the ShowMapper variable, $showMapper (but this can be called anything)
    $showMapper
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
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('user', EntityType::class, ['class' => User::class])
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
      ->add('user.username', null, ['label' => 'User'])
      ->add('uploaded_at', 'doctrine_orm_datetime_range', ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $listMapper
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
   *
   * @return array
   */
  private function encodeFileNameOfPathsArray($paths)
  {
    $encoded_paths = [];
    foreach ($paths as $path)
    {
      $pieces = explode('/', $path);
      $filename = array_pop($pieces);
      $pieces[] = rawurlencode($filename);
      $encoded_paths[] = implode('/', $pieces);
    }

    return $encoded_paths;
  }
}
