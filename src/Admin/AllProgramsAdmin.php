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
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    '_sort_by' => 'uploaded_at',
    '_sort_order' => 'DESC',
  ];

  private ParameterBagInterface $parameter_bag;

  private ScreenshotRepository $screenshot_repository;

  /**
   * AllProgramsAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, ScreenshotRepository $screenshot_repository,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct($code, $class, $baseControllerName);

    $this->screenshot_repository = $screenshot_repository;
    $this->parameter_bag = $parameter_bag;
  }

  /**
   * @param mixed $program
   *
   * @throws \Sonata\AdminBundle\Exception\ModelManagerException
   */
  public function preUpdate($program): void
  {
    /** @var Program $program */
    /** @var ModelManager $model_manager */
    $model_manager = $this->getModelManager();
    $old_program = $model_manager->getEntityManager($this->getClass())
      ->getUnitOfWork()->getOriginalEntityData($program);

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
    $this->checkFlavor();
  }

  public function prePersist($object): void
  {
    $this->checkFlavor();
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectMetadata($object): Metadata
  {
    return new Metadata($object->getName(), $object->getDescription(), $this->getThumbnailImageUrl($object));
  }

  /**
   * @param mixed $object
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
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
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
    $datagridMapper
      ->add('id')
      ->add('name')
      ->add('user.username', null, ['label' => 'Username'])
      ->add('uploaded_at', 'doctrine_orm_datetime_range', ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
      ->add('flavor')
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $flavor_options = $this->parameter_bag->get('themes');

    $choices = [];
    foreach ($flavor_options as $flavor)
    {
      $choices[$flavor] = $flavor;
    }
    $listMapper
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->add('user')
      ->addIdentifier('name', 'string', ['sortable' => false])
      ->add('flavor', 'choice', [
        'editable' => true,
        'sortable' => false,
        'choices' => $choices,
      ])
      ->add('views')
      ->add('downloads')
      ->add('thumbnail', 'string',
        [
          'template' => 'Admin/program_thumbnail_image_list.html.twig',
        ]
      )
      ->add('approved', null, ['editable' => true, 'sortable' => false])
      ->add('visible', null, ['editable' => true, 'sortable' => false])
      ->add('_action', 'actions', ['actions' => [
        'show' => ['template' => 'Admin/CRUD/list__action_show_program_details.html.twig'],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }

  private function checkFlavor(): void
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
