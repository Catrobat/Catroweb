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

  /**
   * Fetches $limit + 1 categories for cursor-based pagination.
   * The caller checks if count > $limit to determine has_more.
   *
   * @return array<MediaCategory>
   */
  public function getCategories(int $limit, ?string $cursor): array
  {
    $offset = $this->decodeCursorToOffset($cursor);

    return $this->category_repository->findPaginated($limit + 1, $offset);
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

  /**
   * Fetches assets with cursor-based pagination.
   * When $cursor is provided, it is decoded to an offset.
   * Fetches $limit + 1 to allow the caller to determine has_more.
   *
   * @return array<MediaAsset>
   */
  public function getAssetsPaginated(
    int $limit,
    ?string $cursor,
    ?string $category_id,
    ?MediaFileType $file_type,
    ?string $flavor,
    ?string $search,
    string $sort_by,
    string $sort_order,
  ): array {
    $offset = $this->decodeCursorToOffset($cursor);
    $category = $category_id ? $this->category_repository->find($category_id) : null;

    return $this->asset_repository->findPaginated(
      $limit + 1,
      $offset,
      $category,
      $file_type,
      $flavor,
      $search,
      $sort_by,
      $sort_order
    );
  }

  /**
   * Fetches assets with raw offset (no cursor, no +1 padding).
   * Used internally by the response manager for preview assets.
   *
   * @return array<MediaAsset>
   */
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

  private function decodeCursorToOffset(?string $cursor): int
  {
    if (null === $cursor) {
      return 0;
    }

    $decoded = base64_decode($cursor, true);
    if (false === $decoded) {
      return 0;
    }

    $offset = (int) $decoded;

    return max(0, $offset);
  }
}
