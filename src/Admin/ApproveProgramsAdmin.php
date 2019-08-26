<?php

namespace App\Admin;

use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Catrobat\Services\ExtractedFileRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * Class ApproveProgramsAdmin
 * @package App\Admin
 */
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

  /**
   * @var null
   */
  private $extractedProgram = null;


  /**
   * @param string $context
   *
   * @return QueryBuilder|\Sonata\AdminBundle\Datagrid\ProxyQueryInterface
   */
  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->eq($query->getRootAliases()[0] . '.approved', $query->expr()->literal(false))
    );
    $query->andWhere(
      $query->expr()->eq($query->getRootAliases()[0] . '.visible', $query->expr()->literal(true))
    );

    return $query;
  }


  /**
   * @param ShowMapper $showMapper
   */
  protected function configureShowFields(ShowMapper $showMapper)
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
      ->add('', null, ['template' => 'Admin/program_approve_action.html.twig']);
  }


  /**
   * @param $program
   *
   * @throws \Sonata\AdminBundle\Exception\ModelManagerException
   */
  public function preUpdate($program)
  {
    /**
     * @var $program Program
     * @var $model_manager ModelManager
     */
    $model_manager = $this->getModelManager();
    $old_program = $model_manager->getEntityManager($this->getClass())->getUnitOfWork()
      ->getOriginalEntityData($program);

    if ($old_program['approved'] == false && $program->getApproved() == true)
    {
      $program->setApprovedByUser($this->getConfigurationPool()->getContainer()
        ->get('security.token_storage')->getToken()->getUser());
      $this->getModelManager()->update($program);
    }
    elseif ($old_program['approved'] == true && $program->getApproved() == false)
    {
      $program->setApprovedByUser(null);
      $this->getModelManager()->update($program);
    }
  }


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('user', EntityType::class, ['class' => User::class]);
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('name')
      ->add('user.username');
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('user')
      ->add('name')
      ->add('description')
      ->add('visible', 'boolean', ['editable' => true])
      ->add('approved', 'boolean', ['editable' => true])
      ->add('_action', 'actions', ['actions' => ['show' => []]]);
  }


  /**
   * @param $object
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    /**
     * @var $object Program
     */

    return '/' . $this->getConfigurationPool()->getContainer()->get('screenshotrepository')
        ->getThumbnailWebPath($object->getId());
  }


  /**
   * @param $object
   *
   * @return array
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function getContainingImageUrls($object)
  {
    /**
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $progManager ProgramManager
     * @var $object Program
     */

    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository
        ->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    $image_paths = $this->extractedProgram->getContainingImagePaths();

    return $this->encodeFileNameOfPathsArray($image_paths);
  }

  /**
   * @param $paths
   *
   * @return array
   */
  private function encodeFileNameOfPathsArray($paths) {
    $encoded_paths = [];
    foreach ($paths as $path) {
      $pieces = explode("/",$path);
      $filename = array_pop($pieces);
      array_push($pieces, rawurlencode($filename));
      array_push($encoded_paths, implode('/', $pieces));
    }
    return $encoded_paths;
  }

  /**
   * @param $object
   *
   * @return array
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function getContainingSoundUrls($object)
  {
    /**
     * @var $extractedFileRepository ExtractedFileRepository
     * @var $progManager ProgramManager
     * @var $object Program
     */

    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository
        ->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    return $this->encodeFileNameOfPathsArray($this->extractedProgram->getContainingSoundPaths());
  }


  /**
   * @param $object
   *
   * @return array
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function getContainingStrings($object)
  {
    /**
     * @var $object Program
     */

    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository
        ->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    return $this->extractedProgram->getContainingStrings();
  }

  /**
   * @param $object
   *
   * @return array
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function getContainingCodeObjects($object)
  {
    /**
     * @var $object Program
     */

    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository
        ->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    if ($this->extractedProgram->hasScenes())
    {
      return [];
    }
    else
    {
      return $this->extractedProgram->getContainingCodeObjects();
    }
  }


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('create')->remove('delete')->remove('edit');
    $collection
      ->add('approve', $this->getRouterIdParameter() . '/approve')
      ->add('invisible', $this->getRouterIdParameter() . '/invisible')
      ->add('skip', $this->getRouterIdParameter() . '/skip');
  }
}
