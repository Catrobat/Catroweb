<?php

namespace App\Admin;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AllProgramsAdmin.
 */
class AllProgramsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_allprogramsadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'all_programs';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'id',
    '_sort_order' => 'DESC',
  ];

  /**
   * @var ScreenshotRepository
   */
  private $screenshot_repository;

  /**
   * AllProgramsAdmin constructor.
   *
   * @param $code
   * @param $class
   * @param $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, ScreenshotRepository $screenshot_repository)
  {
    parent::__construct($code, $class, $baseControllerName);

    $this->screenshot_repository = $screenshot_repository;
  }

  /**
   * @param $program
   *
   * @throws \Sonata\AdminBundle\Exception\ModelManagerException
   */
  public function preUpdate($program)
  {
    /**
     * @var Program
     * @var ModelManager $model_manager
     */
    $model_manager = $this->getModelManager();
    $old_program = $model_manager->getEntityManager($this->getClass())
      ->getUnitOfWork()->getOriginalEntityData($program);

    if (false == $old_program['approved'] && true == $program->getApproved())
    {
      $program->setApprovedByUser($this->getConfigurationPool()->getContainer()
        ->get('security.token_storage')->getToken()->getUser());
      $this->getModelManager()->update($program);
    }
    elseif (true == $old_program['approved'] && false == $program->getApproved())
    {
      $program->setApprovedByUser(null);
      $this->getModelManager()->update($program);
    }
    $this->checkFlavor();
  }

  /**
   * @param $object
   */
  public function prePersist($object)
  {
    $this->checkFlavor();
  }

  /**
   * @param $object
   *
   * @return Metadata
   */
  public function getObjectMetadata($object)
  {
    /*
     * @var $object object
     */
    return new Metadata($object->getName(), $object->getDescription(), $this->getThumbnailImageUrl($object));
  }

  /**
   * @param $object
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    /*
     * @var $object object
     */
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
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
      ->add('description')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('downloads')
      ->add('views')
      ->add('flavor')
      ->add('visible', null, ['required' => false])
      ->add('approved', null, ['required' => false])
    ;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('id')
      ->add('name')
      ->add('downloads')
      ->add('user.username')
    ;
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
      ->add('flavor', 'string', ['editable' => true])
      ->add('views')
      ->add('downloads')
      ->add('thumbnail', 'string',
        [
          'template' => 'Admin/program_thumbnail_image_list.html.twig',
        ]
      )
      ->add('approved', null, ['editable' => true])
      ->add('visible', null, ['editable' => true])
      ->add('_action', 'actions', ['actions' => [
        'show' => ['template' => 'Admin/CRUD/list__action_show_program_details.html.twig'],
        'edit' => [],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }

  private function checkFlavor()
  {
    $flavor = $this->getForm()->get('flavor')->getData();

    if (!$flavor)
    {
      return; // There was no required flavor form field in this Action, so no check is needed!
    }

    $flavor_options = $this->getConfigurationPool()->getContainer()->getParameter('themes');

    if (!in_array($flavor, $flavor_options, true))
    {
      throw new NotFoundHttpException('"'.$flavor.'"Flavor is unknown! Choose either '.implode(',', $flavor_options));
    }
  }
}
