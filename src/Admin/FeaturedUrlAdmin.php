<?php

namespace App\Admin;

use App\Catrobat\Forms\FeaturedImageConstraint;
use App\Catrobat\Services\FeaturedImageRepository;
use App\Entity\FeaturedProgram;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FeaturedUrlAdmin extends AbstractAdmin
{
  /**
   * @override
   *
   * @var string
   */
  protected $baseRouteName = 'admin_featured_url';

  /**
   * @override
   *
   * @var string
   */
  protected $baseRoutePattern = 'featured_url';

  private FeaturedImageRepository $featured_image_repository;

  private ParameterBagInterface $parameter_bag;

  public function __construct($code, $class, $baseControllerName, FeaturedImageRepository $featured_image_repository,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->featured_image_repository = $featured_image_repository;
    $this->parameter_bag = $parameter_bag;
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
    return new Metadata($object->getUrl(), '', $this->getFeaturedImageUrl($object));
  }

  /**
   * @param FeaturedProgram $image
   */
  public function preUpdate($image): void
  {
    $image->old_image_type = $image->getImageType();
    $this->checkFlavor();
  }

  /**
   * @param mixed $object
   */
  public function prePersist($object): void
  {
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
      $qb->expr()->isNull($qb->getRootAliases()[0].'.program')
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
      ], ];

    if (null != $this->getSubject()->getId())
    {
      $file_options['help'] = '<img src="../'.$this->getFeaturedImageUrl($featured_project).'">';
    }

    $formMapper
      ->add('file', FileType::class, $file_options)
      ->add('url', UrlType::class)
      ->add('flavor')
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
        'help' => 'Toggle for iOS featured url api call.', ])
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
      ->add('url')
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
      ->add('url', UrlType::class)
      ->add('flavor', StringType::class, ['editable' => true])
      ->add('priority', IntegerType::class, ['editable' => true])
      ->add('for_ios', null, ['label' => 'iOS only', 'editable' => true])
      ->add('active', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
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
