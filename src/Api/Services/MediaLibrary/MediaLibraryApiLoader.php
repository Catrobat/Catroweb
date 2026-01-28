<?php

declare(strict_types=1);

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\MediaLibrary\MediaFileType;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use App\DB\EntityRepository\MediaLibrary\MediaCategoryRepository;

class MediaLibraryApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly MediaCategoryRepository $category_repository,
    private readonly MediaAssetRepository $asset_repository,
  ) {
  }

  public function getCategoryById(string $id): ?MediaCategory
  {
    return $this->category_repository->find($id);
  }

  public function getCategories(int $limit, int $offset): array
  {
    return $this->category_repository->findPaginated($limit, $offset);
  }

  public function getAllCategories(): array
  {
    return $this->category_repository->findAll();
  }

  public function countCategories(): int
  {
    return $this->category_repository->countAll();
  }

  public function getAssetById(string $id): ?MediaAsset
  {
    return $this->asset_repository->find($id);
  }

  public function getAssets(
    int $limit,
    int $offset,
    ?string $category_id,
    ?MediaFileType $file_type,
    ?string $flavor,
    ?string $search,
    string $sort_by,
    string $sort_order,
  ): array {
    $category = $category_id ? $this->category_repository->find($category_id) : null;

    return $this->asset_repository->findPaginated(
      $limit,
      $offset,
      $category,
      $file_type,
      $flavor,
      $search,
      $sort_by,
      $sort_order
    );
  }

  public function countAssets(
    ?string $category_id,
    ?MediaFileType $file_type,
    ?string $flavor,
    ?string $search,
  ): int {
    $category = $category_id ? $this->category_repository->find($category_id) : null;

    return $this->asset_repository->countAll(
      $category,
      $file_type,
      $flavor,
      $search
    );
  }
}
