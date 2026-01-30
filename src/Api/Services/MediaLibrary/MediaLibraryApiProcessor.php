<?php

declare(strict_types=1);

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\MediaLibrary\MediaFileType;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use App\DB\EntityRepository\MediaLibrary\MediaCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaLibraryApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly MediaCategoryRepository $category_repository,
    private readonly MediaAssetRepository $asset_repository,
  ) {
  }

  public function createCategory(string $name, ?string $description, int $priority): MediaCategory
  {
    $category = $this->category_repository->createCategory($name, $description, $priority);
    $this->entity_manager->flush();

    return $category;
  }

  public function updateCategory(MediaCategory $category, ?string $name, ?string $description, ?int $priority): void
  {
    if (null !== $name) {
      $category->setName($name);
    }

    if (null !== $description) {
      $category->setDescription($description);
    }

    if (null !== $priority) {
      $category->setPriority($priority);
    }

    $this->entity_manager->flush();
  }

  public function deleteCategory(MediaCategory $category): void
  {
    $this->entity_manager->remove($category);
    $this->entity_manager->flush();
  }

  public function createAsset(
    string $name,
    ?string $description,
    MediaCategory $category,
    array $flavors,
    MediaFileType $file_type,
    UploadedFile $file,
    string $extension,
    ?string $author,
  ): MediaAsset {
    $asset = new MediaAsset();
    $asset->setName($name);
    $asset->setDescription($description);
    $asset->setCategory($category);
    $asset->setFileType($file_type);
    $asset->setExtension($extension);
    $asset->setAuthor($author);
    $asset->setActive(true);

    foreach ($flavors as $flavor) {
      $asset->addFlavor($flavor);
    }

    $this->entity_manager->persist($asset);
    $this->entity_manager->flush();

    $this->asset_repository->saveFile($file, $asset->getId(), $asset->getExtension());

    return $asset;
  }

  public function updateAsset(
    MediaAsset $asset,
    ?string $name,
    ?string $description,
    ?MediaCategory $category,
    ?array $flavors,
    ?string $author,
    ?bool $active,
  ): void {
    if (null !== $name) {
      $asset->setName($name);
    }

    if (null !== $description) {
      $asset->setDescription($description);
    }

    if (null !== $category) {
      $asset->setCategory($category);
    }

    if (null !== $flavors) {
      $asset->getFlavors()->clear();
      foreach ($flavors as $flavor) {
        $asset->addFlavor($flavor);
      }
    }

    if (null !== $author) {
      $asset->setAuthor($author);
    }

    if (null !== $active) {
      $asset->setActive($active);
    }

    $this->entity_manager->flush();
  }

  public function deleteAsset(MediaAsset $asset): void
  {
    $this->asset_repository->removeFile($asset->getId(), $asset->getExtension());
    $this->entity_manager->remove($asset);
    $this->entity_manager->flush();
  }
}
