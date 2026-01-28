<?php

declare(strict_types=1);

namespace App\Admin\MediaLibrary;

use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\MediaLibrary\MediaFileType;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @phpstan-extends AbstractAdmin<MediaAsset>
 */
class MediaAssetAdmin extends AbstractAdmin
{
  private MediaAssetRepository $asset_repository;

  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_media_library_asset';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'media-library/asset';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'created_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    $is_create = $this->isCurrentRoute('create');
    $file_type_choices = [];
    foreach (MediaFileType::cases() as $case) {
      $file_type_choices[$case->value] = $case;
    }

    $form
      ->add('name', TextType::class)
      ->add('description', TextareaType::class, ['required' => false])
      ->add('category', EntityType::class, ['class' => MediaCategory::class])
      ->add('file_type', ChoiceType::class, ['choices' => $file_type_choices])
      ->add('file', FileType::class, [
        'required' => $is_create,
      ])
      ->add('extension', TextType::class, ['disabled' => true, 'required' => false])
      ->add('author', TextType::class, ['required' => false])
      ->add('flavors', EntityType::class, [
        'class' => Flavor::class,
        'multiple' => true,
        'required' => false,
      ])
      ->add('downloads', IntegerType::class, ['required' => false])
      ->add('active', CheckboxType::class, ['required' => false])
    ;
  }

  #[Required]
  public function setAssetRepository(MediaAssetRepository $asset_repository): void
  {
    $this->asset_repository = $asset_repository;
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('name')
      ->add('category')
      ->add('file_type')
      ->add('author')
      ->add('active')
      ->add('created_at')
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('name')
      ->add('category')
      ->add('file_type')
      ->add('extension')
      ->add('author')
      ->add('active', null, ['editable' => true])
      ->add('downloads')
      ->add('created_at')
      ->add('updated_at')
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => [], 'delete' => [], 'show' => []]])
    ;
  }

  #[\Override]
  protected function configureShowFields(ShowMapper $show): void
  {
    $show
      ->add('preview', null, [
        'label' => 'Preview',
        'virtual_field' => true,
        'template' => 'Admin/MediaLibrary/AssetPreview.html.twig',
      ])
      ->add('name')
      ->add('description')
      ->add('category')
      ->add('file_type')
      ->add('extension')
      ->add('author')
      ->add('flavors')
      ->add('downloads')
      ->add('active')
      ->add('created_at')
      ->add('updated_at')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list', 'create', 'edit', 'delete', 'show']);
  }

  #[\Override]
  public function prePersist(object $object): void
  {
    $this->prepareUpload($object);
  }

  #[\Override]
  public function postPersist(object $object): void
  {
    $this->storeUpload($object);
  }

  #[\Override]
  public function preUpdate(object $object): void
  {
    $this->prepareUpload($object, $object->getExtension());
  }

  #[\Override]
  public function postUpdate(object $object): void
  {
    $this->storeUpload($object);
  }

  private function prepareUpload(MediaAsset $asset, ?string $old_extension = null): void
  {
    $uploaded_file = $asset->getFile();
    if (!$uploaded_file instanceof UploadedFile) {
      return;
    }

    $extension = $uploaded_file->getClientOriginalExtension();
    if ('' === $extension) {
      $extension = (string) $uploaded_file->guessExtension();
    }
    $extension = '' !== $extension ? strtolower($extension) : 'bin';

    if (null !== $old_extension) {
      $asset->setOldExtension($old_extension);
    }

    $asset->setExtension($extension);
  }

  private function storeUpload(MediaAsset $asset): void
  {
    $uploaded_file = $asset->getFile();
    if (!$uploaded_file instanceof UploadedFile) {
      return;
    }

    $old_extension = $asset->getOldExtension();
    if (null !== $old_extension && $old_extension !== $asset->getExtension()) {
      $this->asset_repository->removeFile($asset->getId(), $old_extension);
    }

    if (null !== $old_extension && $old_extension === $asset->getExtension()) {
      $this->asset_repository->removeFile($asset->getId(), $old_extension);
    }

    $this->asset_repository->saveFile($uploaded_file, $asset->getId(), $asset->getExtension());
  }
}
