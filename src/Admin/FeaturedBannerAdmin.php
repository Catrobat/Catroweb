<?php

declare(strict_types=1);

namespace App\Admin;

use App\DB\Entity\FeaturedBanner;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

/**
 * @phpstan-extends AbstractAdmin<FeaturedBanner>
 */
class FeaturedBannerAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_featured_banner';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'featured/banner';
  }

  public function __construct(
    private readonly ImageRepository $featured_image_repository,
  ) {
  }

  public function getFeaturedImageUrl(FeaturedBanner $object): string
  {
    $id = $object->getId();
    if (null === $id) {
      return '';
    }

    $image_type = $object->getImageType();
    if ('' === $image_type) {
      return '/images/default/screenshot-card@1x.webp';
    }

    return '/'.$this->featured_image_repository->getWebPath($id, $image_type, true);
  }

  #[\Override]
  public function getObjectMetadata($object): MetadataInterface
  {
    /** @var FeaturedBanner $banner */
    $banner = $object;

    return new Metadata(
      $banner->getTitle() ?? 'Banner #'.$banner->getId(),
      $banner->getType(),
      $this->getFeaturedImageUrl($banner),
    );
  }

  #[\Override]
  protected function preUpdate(object $object): void
  {
    /** @var FeaturedBanner $banner */
    $banner = $object;

    $banner->old_image_type = $banner->getImageType();
    $banner->setUpdatedOn(new \DateTime());
  }

  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    /** @var FeaturedBanner $banner */
    $banner = $this->getSubject();
    $isNew = null === $banner->getId();

    $file_options = [
      'required' => $isNew,
    ];

    $ratioHint = 'Recommended: 3:1 ratio (e.g. 1200×400 or 1800×600). Images will be cropped to fit.';
    if (!$isNew) {
      $imageUrl = $this->getFeaturedImageUrl($banner);
      $file_options['help'] = '' !== $imageUrl
        ? '<img src="'.$imageUrl.'" width="300" style="border-radius:6px;margin-bottom:4px;display:block;">'.$ratioHint
        : 'No image uploaded. '.$ratioHint;
    } else {
      $file_options['help'] = $ratioHint;
    }

    $form
      ->add('type', ChoiceType::class, [
        'choices' => [
          'Project' => 'project',
          'Studio' => 'studio',
          'Link' => 'link',
          'Image' => 'image',
        ],
        'required' => true,
      ])
      ->add('program', EntityType::class, [
        'class' => Program::class,
        'choice_label' => 'name',
        'required' => false,
        'help' => 'Select a project (for type "project")',
      ])
      ->add('studio', EntityType::class, [
        'class' => Studio::class,
        'choice_label' => 'name',
        'required' => false,
        'help' => 'Select a studio (for type "studio")',
      ])
      ->add('url', UrlType::class, [
        'required' => false,
        'help' => 'Custom URL (for type "link")',
      ])
      ->add('file', FileType::class, $file_options)
      ->add('title', TextType::class, [
        'required' => false,
        'help' => 'Optional overlay title for the banner',
      ])
      ->add('priority', IntegerType::class, ['required' => false])
      ->add('active', CheckboxType::class, ['required' => false])
    ;
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('type')
      ->add('active')
      ->add('priority')
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    unset($this->getListModes()['mosaic']);
    $list
      ->addIdentifier('id', null, [
        'sortable' => false,
      ])
      ->add('type', 'string', [
        'label' => 'Type',
        'sortable' => true,
      ])
      ->add('Featured Image', null, [
        'accessor' => $this->getFeaturedImageUrl(...),
        'template' => 'Admin/Projects/FeaturedImage.html.twig',
      ])
      ->add('title', 'string')
      ->add('priority', 'integer')
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
