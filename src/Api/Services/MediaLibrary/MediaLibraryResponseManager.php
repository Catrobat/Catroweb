<?php

declare(strict_types=1);

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Traits\CursorPaginationTrait;
use App\Api\Traits\KeysetCursorTrait;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use OpenAPI\Server\Model\MediaAssetResponse;
use OpenAPI\Server\Model\MediaAssetsListResponse;
use OpenAPI\Server\Model\MediaCategoriesListResponse;
use OpenAPI\Server\Model\MediaCategoryDetailResponse;
use OpenAPI\Server\Model\MediaCategoryResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryResponseManager extends AbstractResponseManager
{
  use CursorPaginationTrait;
  use KeysetCursorTrait;

  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    \Psr\Cache\CacheItemPoolInterface $cache,
    private readonly MediaLibraryApiLoader $loader,
    private readonly MediaAssetRepository $asset_repository,
    private readonly ?UrlHelper $url_helper,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  public function createCategoriesResponse(array $categories, int $limit, ?string $cursor): MediaCategoriesListResponse
  {
    $has_more = count($categories) > $limit;
    if ($has_more) {
      array_pop($categories);
    }

    $category_responses = array_map(
      $this->createCategoryResponse(...),
      $categories
    );

    $next_cursor = null;
    if ($has_more && [] !== $categories) {
      $last = end($categories);
      $next_cursor = base64_encode((string) $last->getId());
    }

    return new MediaCategoriesListResponse([
      'data' => $category_responses,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
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

  /**
   * Keyset cursor response for categories ordered by priority ASC, id ASC.
   */
  public function createCategoriesKeysetResponse(array $categories, int $limit): MediaCategoriesListResponse
  {
    $has_more = count($categories) > $limit;
    if ($has_more) {
      array_pop($categories);
    }

    $category_responses = array_map(
      $this->createCategoryResponse(...),
      $categories
    );

    $next_cursor = null;
    if ($has_more && [] !== $categories) {
      /** @var MediaCategory $last */
      $last = end($categories);
      $next_cursor = $this->encodeIntKeysetCursor($last->getPriority(), $last->getId());
    }

    return new MediaCategoriesListResponse([
      'data' => $category_responses,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  /**
   * Keyset cursor response for assets.
   */
  public function createAssetsKeysetResponse(array $assets, int $limit, string $sort_by): MediaAssetsListResponse
  {
    $has_more = count($assets) > $limit;
    if ($has_more) {
      array_pop($assets);
    }

    $asset_responses = array_map(
      fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
      $assets
    );

    $next_cursor = null;
    if ($has_more && [] !== $assets) {
      /** @var MediaAsset $last */
      $last = end($assets);
      $sort_value = match ($sort_by) {
        'name' => $last->getName(),
        'downloads' => (string) $last->getDownloads(),
        'updated_at' => $last->getUpdatedAt()?->format('Y-m-d\TH:i:s.uP') ?? '',
        default => $last->getCreatedAt()?->format('Y-m-d\TH:i:s.uP') ?? '',
      };
      $next_cursor = $this->encodeKeysetCursor($sort_value, $last->getId());
    }

    return new MediaAssetsListResponse([
      'data' => $asset_responses,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  /**
   * Keyset cursor response for category detail (assets within a category).
   */
  public function createCategoryDetailKeysetResponse(MediaCategory $category, array $assets, int $limit): MediaCategoryDetailResponse
  {
    $has_more = count($assets) > $limit;
    if ($has_more) {
      array_pop($assets);
    }

    $asset_responses = array_map(
      fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
      $assets
    );

    $next_cursor = null;
    if ($has_more && [] !== $assets) {
      /** @var MediaAsset $last */
      $last = end($assets);
      $sort_value = $last->getCreatedAt()?->format('Y-m-d\TH:i:s.uP') ?? '';
      $next_cursor = $this->encodeKeysetCursor($sort_value, $last->getId());
    }

    return new MediaCategoryDetailResponse([
      'id' => $category->getId(),
      'name' => $this->trans($category->getName()),
      'description' => $category->getDescription() ? $this->trans($category->getDescription()) : null,
      'priority' => $category->getPriority(),
      'created_at' => $category->getCreatedAt(),
      'updated_at' => $category->getUpdatedAt(),
      'data' => $asset_responses,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  public function createCategoryDetailResponse(MediaCategory $category, array $assets, int $limit, int $current_offset = 0): MediaCategoryDetailResponse
  {
    $has_more = count($assets) > $limit;
    if ($has_more) {
      array_pop($assets);
    }

    $asset_responses = array_map(
      fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
      $assets
    );

    $next_cursor = $has_more ? $this->encodeCursorFromOffset($current_offset, count($assets)) : null;

    return new MediaCategoryDetailResponse([
      'id' => $category->getId(),
      'name' => $this->trans($category->getName()),
      'description' => $category->getDescription() ? $this->trans($category->getDescription()) : null,
      'priority' => $category->getPriority(),
      'created_at' => $category->getCreatedAt(),
      'updated_at' => $category->getUpdatedAt(),
      'data' => $asset_responses,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  public function createAssetsResponse(array $assets, int $limit, int $current_offset = 0): MediaAssetsListResponse
  {
    $has_more = count($assets) > $limit;
    if ($has_more) {
      array_pop($assets);
    }

    $asset_responses = array_map(
      fn (MediaAsset $asset): MediaAssetResponse => $this->createAssetResponse($asset),
      $assets
    );

    $next_cursor = $has_more ? $this->encodeCursorFromOffset($current_offset, count($assets)) : null;

    return new MediaAssetsListResponse([
      'data' => $asset_responses,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
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
    int $limit,
    ?string $cursor,
    int $assets_per_category,
    ?\App\DB\Entity\MediaLibrary\MediaFileType $file_type,
    ?string $flavor,
    ?string $search = null,
  ): \OpenAPI\Server\Model\MediaLibraryResponse {
    $search = null !== $search ? trim($search) : null;
    if ('' === $search) {
      $search = null;
    }

    $current_offset = $this->decodeCursorToOffset($cursor);

    if (null === $search) {
      $has_more = count($categories) > $limit;
      if ($has_more) {
        array_pop($categories);
      }

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

      $category_previews = array_slice($category_previews, $current_offset, $limit + 1);
      $has_more = count($category_previews) > $limit;
      if ($has_more) {
        array_pop($category_previews);
      }
    }

    $next_cursor = ($has_more && [] !== $category_previews)
      ? $this->encodeCursorFromOffset($current_offset, count($category_previews))
      : null;

    return new \OpenAPI\Server\Model\MediaLibraryResponse([
      'data' => $category_previews,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ]);
  }

  private function convertFileType(\App\DB\Entity\MediaLibrary\MediaFileType $file_type): string
  {
    return $file_type->value;
  }
}
