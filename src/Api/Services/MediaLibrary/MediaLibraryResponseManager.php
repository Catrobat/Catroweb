<?php

declare(strict_types=1);

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use OpenAPI\Server\Model\MediaAssetResponse;
use OpenAPI\Server\Model\MediaAssetsResponse;
use OpenAPI\Server\Model\MediaCategoriesResponse;
use OpenAPI\Server\Model\MediaCategoryDetailResponse;
use OpenAPI\Server\Model\MediaCategoryResponse;
use OpenAPI\Server\Model\PaginationInfo;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    \Psr\Cache\CacheItemPoolInterface|\Symfony\Contracts\Cache\CacheInterface $cache,
    private readonly MediaLibraryApiLoader $loader,
    private readonly MediaAssetRepository $asset_repository,
    private readonly ?UrlHelper $url_helper,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  public function createPaginationInfo(int $total, int $limit, int $offset): PaginationInfo
  {
    return new PaginationInfo([
      'total' => $total,
      'limit' => $limit,
      'offset' => $offset,
    ]);
  }

  public function createCategoriesResponse(array $categories, int $total, int $limit, int $offset): MediaCategoriesResponse
  {
    $category_responses = array_map(
      $this->createCategoryResponse(...),
      $categories
    );

    return new MediaCategoriesResponse([
      'categories' => $category_responses,
      'pagination' => $this->createPaginationInfo($total, $limit, $offset),
    ]);
  }

  public function createCategoryResponse(MediaCategory $category): MediaCategoryResponse
  {
    return new MediaCategoryResponse([
      'id' => $category->getId(),
      'name' => $this->trans($category->getName()),
      'description' => $category->getDescription() ? $this->trans($category->getDescription()) : null,
      'priority' => $category->getPriority(),
    ]);
  }

  public function createCategoryDetailResponse(MediaCategory $category, array $assets, int $total_assets, int $limit, int $offset): MediaCategoryDetailResponse
  {
    $asset_responses = array_map(
      fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
      $assets
    );

    return new MediaCategoryDetailResponse([
      'id' => $category->getId(),
      'name' => $this->trans($category->getName()),
      'description' => $category->getDescription() ? $this->trans($category->getDescription()) : null,
      'priority' => $category->getPriority(),
      'assets' => $asset_responses,
      'pagination' => $this->createPaginationInfo($total_assets, $limit, $offset),
    ]);
  }

  public function createAssetsResponse(array $assets, int $total, int $limit, int $offset): MediaAssetsResponse
  {
    $asset_responses = array_map(
      fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
      $assets
    );

    return new MediaAssetsResponse([
      'assets' => $asset_responses,
      'pagination' => $this->createPaginationInfo($total, $limit, $offset),
    ]);
  }

  public function createAssetResponse(MediaAsset $asset, ?User $user = null): MediaAssetResponse
  {
    $base_url = $this->url_helper?->getAbsoluteUrl('/') ?? '';
    $download_path = $this->asset_repository->getWebPath($asset->getId(), $asset->getExtension());
    $download_url = $base_url.$download_path;

    $thumbnail_url = null;
    if ($asset->isImage()) {
      $thumbnail_path = $this->asset_repository->getThumbnailWebPath($asset->getId(), $asset->getExtension());
      $thumbnail_url = $base_url.$thumbnail_path;
    }

    // Calculate file size
    $size = null;
    try {
      $file_path = $this->asset_repository->getFilePath($asset->getId(), $asset->getExtension());
      if (file_exists($file_path)) {
        $size = filesize($file_path);
      }
    } catch (\Exception) {
      // If file doesn't exist or can't be accessed, size remains null
    }

    return new MediaAssetResponse([
      'id' => $asset->getId(),
      'name' => $asset->getName(),
      'description' => $asset->getDescription(),
      'category_id' => $asset->getCategory()->getId(),
      'category_name' => $this->trans($asset->getCategory()->getName()),
      'file_type' => $this->convertFileType($asset->getFileType()),
      'extension' => $asset->getExtension(),
      'size' => $size,
      'author' => $asset->getAuthor(),
      'downloads' => $asset->getDownloads(),
      'active' => $asset->getActive(),
      'flavors' => array_map(fn ($flavor) => $flavor->getName(), $asset->getFlavors()->toArray()),
      'download_url' => $download_url,
      'thumbnail_url' => $thumbnail_url,
      'created_at' => $asset->getCreatedAt(),
      'updated_at' => $asset->getUpdatedAt(),
    ]);
  }

  /**
   * @param MediaCategory[] $categories
   */
  public function createLibraryOverviewResponse(
    array $categories,
    int $total_categories,
    int $limit,
    int $offset,
    int $assets_per_category,
    ?\App\DB\Entity\MediaLibrary\MediaFileType $file_type,
    ?string $flavor,
    ?string $search = null,
  ): \OpenAPI\Server\Model\MediaLibraryResponse {
    $search = null !== $search ? trim($search) : null;
    if ('' === $search) {
      $search = null;
    }

    if (null === $search) {
      $category_previews = array_map(
        function (MediaCategory $category) use ($assets_per_category, $file_type, $flavor): \OpenAPI\Server\Model\MediaLibraryCategoryPreview {
          // Get preview assets for this category
          $preview_assets = $this->loader->getAssets(
            $assets_per_category,
            0,
            $category->getId(),
            $file_type,
            $flavor,
            null,
            'created_at',
            'DESC'
          );

          $total_assets = $this->loader->countAssets($category->getId(), $file_type, $flavor, null);

          $preview_responses = array_map(
            fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
            $preview_assets
          );

          return new \OpenAPI\Server\Model\MediaLibraryCategoryPreview([
            'id' => $category->getId(),
            'name' => $this->trans($category->getName()),
            'description' => $category->getDescription() ? $this->trans($category->getDescription()) : null,
            'priority' => $category->getPriority(),
            'assets_count' => $total_assets,
            'preview_assets' => $preview_responses,
          ]);
        },
        $categories
      );
    } else {
      $category_previews = [];
      foreach ($categories as $category) {
        $translated_name = $this->trans($category->getName());
        $translated_description = $category->getDescription() ? $this->trans($category->getDescription()) : '';
        $search_lower = strtolower($search);
        $name_match = str_contains(strtolower($translated_name), $search_lower);
        $desc_match = '' !== $translated_description && str_contains(strtolower($translated_description), $search_lower);

        $asset_search = ($name_match || $desc_match) ? null : $search;

        $preview_assets = $this->loader->getAssets(
          $assets_per_category,
          0,
          $category->getId(),
          $file_type,
          $flavor,
          $asset_search,
          'created_at',
          'DESC'
        );

        $total_assets = $this->loader->countAssets($category->getId(), $file_type, $flavor, $asset_search);
        if (0 === $total_assets && !$name_match && !$desc_match) {
          continue;
        }

        $preview_responses = array_map(
          fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
          $preview_assets
        );

        $category_previews[] = new \OpenAPI\Server\Model\MediaLibraryCategoryPreview([
          'id' => $category->getId(),
          'name' => $translated_name,
          'description' => '' !== $translated_description ? $translated_description : null,
          'priority' => $category->getPriority(),
          'assets_count' => $total_assets,
          'preview_assets' => $preview_responses,
        ]);
      }

      $total_categories = count($category_previews);
      $category_previews = array_slice($category_previews, $offset, $limit);
    }

    return new \OpenAPI\Server\Model\MediaLibraryResponse([
      'categories' => $category_previews,
      'pagination' => $this->createPaginationInfo($total_categories, $limit, $offset),
    ]);
  }

  private function convertFileType(\App\DB\Entity\MediaLibrary\MediaFileType $file_type): string
  {
    return $file_type->value;
  }
}
