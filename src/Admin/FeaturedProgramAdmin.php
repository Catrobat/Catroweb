<?php

namespace App\Admin;

use App\Catrobat\Forms\FeaturedImageConstraint;
use App\Catrobat\Services\FeaturedImageRepository;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FeaturedProgramAdmin extends AbstractAdmin
{
  /**
   * @override
   *
   * @var string
   */
  protected $baseRouteName = 'adminfeatured_program';

  /**
   * @override
   *
   * @var string
   */
  protected $baseRoutePattern = 'featured_program';

  private EntityManagerInterface $entity_manager;

  private ParameterBagInterface $parameter_bag;

  private FeaturedImageRepository $featured_image_repository;

  private ProgramManager $program_manager;

  /**
   * FeaturedProgramAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, EntityManagerInterface $entity_manager,
                              ParameterBagInterface $parameter_bag, FeaturedImageRepository $featured_image_repository,
                              ProgramManager $program_manager)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->entity_manager = $entity_manager;
    $this->parameter_bag = $parameter_bag;
    $this->featured_image_repository = $featured_image_repository;
    $this->program_manager = $program_manager;
  }

  /**
   * @param FeaturedProgram $object
   *
   * @return string
   */
  public function getFeaturedImageUrl($object)
  {
    return '../../'.$this->featured_image_repository->getWebPath($object->getId(), $object->getImageType());
  }

  /**
   * @param FeaturedProgram $object
   *
   * @return Metadata
   */
  public function getObjectMetadata($object)
  {
    return new Metadata($object->getProgram()->getName(), $object->getProgram()->getDescription(),
      $this->getFeaturedImageUrl($object));
  }

  /**
   * @param FeaturedProgram $object
   */
  public function preUpdate($object): void
  {
    $object->old_image_type = $object->getImageType();
    $this->checkProgramID($object);
    $this->checkFlavor();
  }

  /**
   * @param mixed $object
   */
  public function prePersist($object): void
  {
    $this->checkProgramID($object);
    $this->checkFlavor();
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
      $qb->expr()->isNotNull($qb->getRootAliases()[0].'.program')
    );

    return $query;
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    /** @var FeaturedProgram $featured_project */
    $featured_project = $this->getSubject();
    $file_options = [
      'required' => (null === $featured_project->getId()),
      'constraints' => [
        new FeaturedImageConstraint(),
      ],
    ];

    $id_value = '';

    if (null !== $this->getSubject()->getId())
    {
      $file_options['help'] = '<img src="../'.$this->getFeaturedImageUrl($featured_project).'">';
      $id_value = $this->getSubject()->getProgram()->getId();
    }

    $formMapper
      ->add('file', FileType::class, $file_options)
      ->add('program_id', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor')
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
        'help' => 'Toggle for iOS featured programs api call.', ])
      ->add('active', null, ['required' => false])
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
      ->add('program.name')
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
      ->addIdentifier('id')
      ->add('Featured Image', 'string', ['template' => 'Admin/featured_image.html.twig'])
      ->add('program', EntityType::class, [
        'class' => Program::class,
        'route' => ['name' => 'show'],
        'admin_code' => 'catrowebadmin.block.programs.all',
      ])
      ->add('flavor', 'string')
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active', null)
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  /**
   * @param FeaturedProgram $object
   */
  private function checkProgramID($object): void
  {
    $id = $this->getForm()->get('program_id')->getData();

    $program = $this->program_manager->find($id);

    if (null !== $program)
    {
      $object->setProgram($program);
    }
    else
    {
      throw new NotFoundHttpException(sprintf('Unable to find program with id : %s', $id));
    }
  }

  private function checkFlavor(): void
  {
    $flavor = $this->getForm()->get('flavor')->getData();

    if (!$flavor)
    {
      return; // There was no required flavor form field in this Action, so no check is needed!
    }

    $flavor_options = $this->parameter_bag->get('themes');

    if (!in_array($flavor, $flavor_options, true))
    {
      throw new NotFoundHttpException('"'.$flavor.'"Flavor is unknown! Choose either '.implode(',', $flavor_options));
    }
  }
}
