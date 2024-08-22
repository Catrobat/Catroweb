<?php

declare(strict_types=1);

namespace App\Admin\Projects;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @phpstan-extends AbstractAdmin<Program>
 */
class ProjectsAdmin extends AbstractAdmin
{
  use ProjectPreUpdateTrait;

  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_projects';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'project';
  }

  protected const int SAFE_FOR_KIDS = 0;

  protected const int NOT_FOR_KIDS_MOD = 2;

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'uploaded_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  public function __construct(
    private readonly ScreenshotRepository $screenshot_repository,
    protected TokenStorageInterface $security_token_storage,
    private readonly ParameterBagInterface $parameter_bag
  ) {
  }

  #[\Override]
  public function getObjectMetadata($object): MetadataInterface
  {
    return new Metadata($object->getName(), $object->getDescription(), $this->getThumbnailImageUrl($object));
  }

  public function getThumbnailImageUrl(mixed $object): string
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('description')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('flavor')
      ->add('visible', null, ['required' => false])
      ->add('approved', null, ['required' => false])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('name')
      ->add('user.username', null, ['label' => 'Username'])
      ->add('uploaded_at', DateTimeRangeFilter::class, ['field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time', ])
      ->add('flavor')
      ->add('approved')
      ->add('visible')
      ->add('not_for_kids', NumberFilter::class, [
        'field_type' => ChoiceType::class,
        'field_options' => [
          'choices' => [
            'Safe for kids' => self::SAFE_FOR_KIDS,
            'Not for kids' => self::NOT_FOR_KIDS_MOD,
          ],
        ],
      ])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $flavor_options = $this->parameter_bag->get('flavors');

    $choices = [];
    if (is_array($flavor_options)) {
      foreach ($flavor_options as $flavor) {
        $choices[$flavor] = $flavor;
      }
    }

    $list
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->addIdentifier('name', 'string', ['sortable' => false])
      ->add('user')
      ->add('flavor', 'choice', [
        'editable' => true,
        'sortable' => false,
        'choices' => $choices,
      ])
      ->add('views')
      ->add('downloads')
      ->add('thumbnail', 'string',
        [
          'accessor' => fn ($subject): string => $this->getThumbnailImageUrl($subject),
          'template' => 'Admin/project_thumbnail_image_list.html.twig',
        ]
      )
      ->add('private', null, ['editable' => false, 'sortable' => false])
      ->add('approved', null, ['editable' => true, 'sortable' => false])
      ->add('visible', null, ['editable' => true, 'sortable' => false])
      ->add('not_for_kids', 'choice', [
        'editable' => true,
        'sortable' => false,
        'choices' => [
          self::SAFE_FOR_KIDS => 'Safe for kids',
          self::NOT_FOR_KIDS_MOD => 'Not for kids',
        ],
      ])
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'show' => ['template' => 'Admin/CRUD/list__action_show_project_details.html.twig'],
      ]])
    ;
  }

  #[\Override]
  protected function configureShowFields(ShowMapper $show): void
  {
    $flavor_options = $this->parameter_bag->get('flavors');

    $choices = [];
    if (is_array($flavor_options)) {
      foreach ($flavor_options as $flavor) {
        $choices[$flavor] = $flavor;
      }
    }

    $show
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->add('user')
      ->add('flavor', 'choice', [
        'editable' => true,
        'sortable' => false,
        'choices' => $choices,
      ])
      ->add('views')
      ->add('downloads')
      ->add('thumbnail', 'string',
        [
          'accessor' => fn ($subject): string => $this->getThumbnailImageUrl($subject),
          'template' => 'Admin/project_thumbnail_image_list.html.twig',
        ]
      )
      ->add('private', null, ['editable' => false, 'sortable' => false])
      ->add('approved', null, ['editable' => true, 'sortable' => false])
      ->add('visible', null, ['editable' => true, 'sortable' => false])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
