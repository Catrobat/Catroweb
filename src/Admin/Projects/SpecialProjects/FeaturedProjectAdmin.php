<?php

declare(strict_types=1);

namespace App\Admin\Projects\SpecialProjects;

use App\Admin\Projects\SpecialProjects\Forms\FeaturedImageConstraint;
use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\Project\ProjectManager;
use App\Storage\ImageRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormError;

/**
 * @phpstan-extends AbstractAdmin<FeaturedProgram>
 */
class FeaturedProjectAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_featured_project';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'project/featured';
  }

  public function __construct(
    private readonly ImageRepository $featured_image_repository,
    private readonly ProjectManager $project_manager,
  ) {
  }

  public function getFeaturedImageUrl(FeaturedProgram $object): string
  {
    return '../../'.$this->featured_image_repository->getWebPath($object->getId(), $object->getImageType(), true);
  }

  #[\Override]
  public function getObjectMetadata($object): MetadataInterface
  {
    /** @var FeaturedProgram $featured_project */
    $featured_project = $object;

    return new Metadata($featured_project->getProgram()->getName(), $featured_project->getProgram()->getDescription(),
      $this->getFeaturedImageUrl($featured_project));
  }

  #[\Override]
  protected function preUpdate(object $object): void
  {
    /** @var FeaturedProgram $featured_project */
    $featured_project = $object;

    $featured_project->old_image_type = $featured_project->getImageType();
  }

  #[\Override]
  protected function preValidate(object $object): void
  {
    $id = $this->getForm()->get('Program_Id_or_Url')->getData();

    if ($this->getForm()->get('Use_Url')->getData()) {
      // Check if this is a project URL (contains /project/) - extract project ID
      if (null !== $id && str_contains((string) $id, '/project/')) {
        $projectId = preg_replace('$(.*)/project/$', '', (string) $id);
        $project = $this->project_manager->find($projectId);

        if (null !== $project) {
          $object->setProgram($project);
          if (null !== $object->getURL()) {
            $object->setURL(null);
          }
        } else {
          $this->getForm()->addError(new FormError('Unable to find project with given ID.'));
        }
      } elseif (filter_var($id, FILTER_VALIDATE_URL)) {
        $object->setUrl($id);
        if (null !== $object->getId()) {
          $object->setProgram(null);
        }
      } else {
        $this->getForm()->addError(new FormError('Please enter a valid URL.'));
      }
    } else {
      if (null !== $id) {
        $id = preg_replace('$(.*)/project/$', '', (string) $id);
      }

      $project = $this->project_manager->find($id);

      if (null !== $project) {
        $object->setProgram($project);
        if (null !== $object->getURL()) {
          $object->setURL(null);
        }
      } else {
        $this->getForm()->addError(new FormError('Unable to find project with given ID.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  #[\Override]
  protected function configureFormFields(FormMapper $form): void
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
    $use_url = false;

    if (null !== $this->getSubject()->getId()) {
      $file_options['help'] = '<img src="../'.$this->getFeaturedImageUrl($featured_project).'">';

      $id_value = $this->getSubject()->getUrl();
      $use_url = true;
      if (null == $id_value && !is_null($this->getSubject()->getProgram())) {
        $id_value = $this->getSubject()->getProgram()->getId();
        $use_url = false;
      }
    } else {
      $file_options = ['help' => 'The featured image must be of size 1024 x 400'];
    }

    $form
      ->add('file', FileType::class, $file_options)
      ->add('Use_Url', CheckboxType::class, ['mapped' => false, 'required' => false,
        'help' => 'Toggle to save URL instead of Program ID.', 'data' => $use_url, ])
      ->add('Program_Id_or_Url', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor', null, ['class' => Flavor::class, 'multiple' => false, 'required' => true])
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
        'help' => 'Toggle for iOS featured programs api call.', ])
      ->add('active', null, ['required' => false])
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
      ->add('program.name')
      ->add('for_ios')
      ->add('active')
      ->add('priority')
      ->add('flavor')
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
    unset($this->getListModes()['mosaic']);
    $list
      ->addIdentifier('id', null, [
        'sortable' => false,
      ])
      ->add('Featured Image', null, [
        'accessor' => fn ($subject): string => $this->getFeaturedImageUrl($subject),
        'template' => 'Admin/Projects/FeaturedImage.html.twig',
      ])
      ->add('program', EntityType::class, [
        'class' => Program::class,
        'editable' => false,
      ])
      ->add('url', UrlType::class)
      ->add('flavor', 'string', [
        'sortable' => false,
      ])
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active')
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('acl');
  }
}
