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
use Symfony\Component\Routing\RouterInterface;


/**
 * Class FeaturedProgramAdmin
 * @package App\Admin
 */
class FeaturedProgramAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'adminfeatured_program';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'featured_program';

  /**
   * @var
   */
  private $entity_manager;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * @var
   */
  private $featured_image_repository;

  /**
   * FeaturedProgramAdmin constructor.
   *
   * @param $code
   * @param $class
   * @param $baseControllerName
   * @param EntityManagerInterface $entity_manager
   * @param ParameterBagInterface $parameter_bag
   * @param FeaturedImageRepository $featured_image_repository
   */
  public function __construct($code, $class, $baseControllerName, EntityManagerInterface $entity_manager,
                              ParameterBagInterface $parameter_bag, FeaturedImageRepository $featured_image_repository)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->entity_manager = $entity_manager;
    $this->parameter_bag = $parameter_bag;
    $this->featured_image_repository = $featured_image_repository;
  }

  /**
   * @param string $context
   *
   * @return QueryBuilder|ProxyQueryInterface
   */
  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->isNotNull($query->getRootAliases()[0] . '.program')
    );

    return $query;
  }


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $file_options = [
      'required'    => ($this->getSubject()->getId() === null),
      'constraints' => [
        new FeaturedImageConstraint(),
      ],
    ];

    $id_value = '';

    if ($this->getSubject()->getId() !== null)
    {
      $file_options['help'] = '<img src="../' . $this->getFeaturedImageUrl($this->getSubject()) . '">';
      $id_value = $this->getSubject()->getProgram()->getId();
    }

    $formMapper
      ->add('file', FileType::class, $file_options)
      ->add('program_id', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor')
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
                              'help'  => 'Toggle for iOS featured programs api call.'])
      ->add('active', null, ['required' => false]);
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('program.name');
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
      ->add('Featured Image', 'string', ['template' => 'Admin/featured_image.html.twig'])
      ->add('program', EntityType::class, [
        'class'      => Program::class,
        'route'      => ['name' => 'show'],
        'admin_code' => 'catrowebadmin.block.programs.all',
      ])
      ->add('flavor', 'string')
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active', null)
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }


  /**
   * @param $object FeaturedProgram
   *
   * @return string
   */
  public function getFeaturedImageUrl($object)
  {
    return '../../' . $this->featured_image_repository->getWebPath($object->getId(), $object->getImageType());
  }


  /**
   * @param $object FeaturedProgram
   *
   * @return Metadata
   */
  public function getObjectMetadata($object)
  {
    return new Metadata($object->getProgram()->getName(), $object->getProgram()->getDescription(),
      $this->getFeaturedImageUrl($object));
  }


  /**
   * @param $object FeaturedProgram
   */
  public function preUpdate($object)
  {
    $object->old_image_type = $object->getImageType();
    $object->setImageType(null);
    $this->checkProgramID($object);
    $this->checkFlavor();
  }


  /**
   * @param $object
   */
  public function prePersist($object)
  {
    $this->checkProgramID($object);
    $this->checkFlavor();
  }


  /**
   * @param $object FeaturedProgram
   */
  private function checkProgramID($object)
  {
    /**
     * @var $program         Program
     * @var $program_manager ProgramManager
     */

    $id = $this->getForm()->get('program_id')->getData();

    $program_manager = $this->entity_manager->getRepository('\App\Entity\Program');
    $program = $program_manager->find($id);

    if ($program)
    {
      $object->setProgram($program);
    }
    else
    {
      throw new NotFoundHttpException(sprintf('Unable to find program with id : %s', $id));
    }
  }

  /**
   *
   */
  private function checkFlavor()
  {
    $flavor = $this->getForm()->get('flavor')->getData();
    $flavor_options =  $this->parameter_bag->get('themes');

    if (!in_array($flavor, $flavor_options)) {
      throw new NotFoundHttpException(
        '"' . $flavor . '"Flavor is unknown! Choose either ' . implode(",", $flavor_options)
      );
    }
  }
}
