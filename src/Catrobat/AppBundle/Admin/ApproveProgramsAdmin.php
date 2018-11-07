<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Services\ExtractedFileRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ApproveProgramsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_approve_programs';
  protected $baseRoutePattern = 'approve';

  private $extractedProgram = null;

  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->eq($query->getRootAlias() . '.approved', $query->expr()->literal(false))
    );
    $query->andWhere(
      $query->expr()->eq($query->getRootAlias() . '.visible', $query->expr()->literal(true))
    );

    return $query;
  }

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

  public function preUpdate($program)
  {
    /**
     * @var $program Program
     */
    $old_program = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($program);

    if ($old_program['approved'] == false && $program->getApproved() == true)
    {
      $program->setApprovedByUser($this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser());
      $this->getModelManager()->update($program);
    }
    elseif ($old_program['approved'] == true && $program->getApproved() == false)
    {
      $program->setApprovedByUser(null);
      $this->getModelManager()->update($program);
    }
  }

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('user', EntityType::class, ['class' => User::class]);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('name')
      ->add('user.username');
  }

  // Fields to be shown on lists
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

  public function getThumbnailImageUrl($object)
  {
    return '/' . $this->getConfigurationPool()->getContainer()->get('screenshotrepository')->getThumbnailWebPath($object->getId());
  }

  public function getContainingImageUrls($object)
  {

    /* @var $extractedFileRepository ExtractedFileRepository */
    /* @var $progManager ProgramManager */

    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    return $this->extractedProgram->getContainingImagePaths();
  }

  public function getContainingSoundUrls($object)
  {
    /* @var $extractedFileRepository ExtractedFileRepository */
    /* @var $progManager ProgramManager */

    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    return $this->extractedProgram->getContainingSoundPaths();
  }

  public function getContainingStrings($object)
  {
    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
    }

    return $this->extractedProgram->getContainingStrings();
  }

  public function getContainingCodeObjects($object)
  {
    if ($this->extractedProgram == null)
    {
      $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
      $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
      $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
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

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('create')->remove('delete')->remove('edit');
    $collection
      ->add('approve', $this->getRouterIdParameter() . '/approve')
      ->add('invisible', $this->getRouterIdParameter() . '/invisible')
      ->add('skip', $this->getRouterIdParameter() . '/skip');
  }
}
