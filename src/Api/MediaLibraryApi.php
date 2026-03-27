<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaFileType as DbMediaFileType;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\FlavorRepository;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaAssetResponse;
use OpenAPI\Server\Model\MediaAssetsResponse;
use OpenAPI\Server\Model\MediaAssetUpdateRequest;
use OpenAPI\Server\Model\MediaCategoriesResponse;
use OpenAPI\Server\Model\MediaCategoryDetailResponse;
use OpenAPI\Server\Model\MediaCategoryRequest;
use OpenAPI\Server\Model\MediaCategoryResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class MediaLibraryApi extends AbstractApiController implements MediaLibraryApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly MediaLibraryApiFacade $facade,
    private readonly FlavorRepository $flavor_repository,
    private readonly RateLimiterFactory $mediaLibraryBurstLimiter,
    private readonly RequestStack $request_stack,
  ) {
  }

  #[\Override]
  public function mediaLibraryGet(
    string $accept_language,
    int $limit,
    int $offset,
    ?string $file_type,
    ?string $flavor,
    ?string $search,
    int $assets_per_category,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (!$this->checkIpRateLimit($ip, $this->mediaLibraryBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $this->mediaLibraryBurstLimiter, $ip);

    $db_file_type = $file_type ? $this->convertToDbFileType($file_type) : null;
    $search = null !== $search ? trim($search) : null;
    if ('' === $search) {
      $search = null;
    }

    if (null !== $search) {
      $categories = $this->facade->getLoader()->getAllCategories();
      $total_categories = count($categories);
    } else {
      $categories = $this->facade->getLoader()->getCategories($limit, $offset);
      $total_categories = $this->facade->getLoader()->countCategories();
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createLibraryOverviewResponse(
      $categories,
      $total_categories,
      $limit,
      $offset,
      $assets_per_category,
      $db_file_type,
      $flavor,
      $search
    );
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaCategoriesGet(
    string $accept_language,
    int $limit,
    int $offset,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaCategoriesResponse {
    $locale = $this->facade->getResponseManager()->sanitizeLocale($accept_language);
    $cache_id = sprintf('mediaCategoriesGet_%s_%d_%d', $locale, $limit, $offset);

    $cached = $this->facade->getResponseManager()->getCachedResponse($cache_id);
    if (null !== $cached) {
      $responseCode = $cached['response_code'];
      $responseHeaders = $cached['response_headers'];

      return $cached['response'];
    }

    $categories = $this->facade->getLoader()->getCategories($limit, $offset);
    $total = $this->facade->getLoader()->countCategories();

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createCategoriesResponse($categories, $total, $limit, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaCategoriesPost(
    MediaCategoryRequest $media_category_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaCategoryResponse {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User || !$user->hasRole(User::ROLE_MEDIA_ADMIN)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $name = trim($media_category_request->getName() ?? '');
    if ('' === $name) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $description = $media_category_request->getDescription();
    $priority = $media_category_request->getPriority() ?? 0;

    $category = $this->facade->getProcessor()->createCategory($name, $description, $priority);

    $responseCode = Response::HTTP_CREATED;
    $response = $this->facade->getResponseManager()->createCategoryResponse($category);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaCategoriesIdGet(
    string $id,
    string $accept_language,
    int $limit,
    int $offset,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaCategoryDetailResponse {
    $category = $this->facade->getLoader()->getCategoryById($id);
    if (!$category instanceof \App\DB\Entity\MediaLibrary\MediaCategory) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $assets = $this->facade->getLoader()->getAssets($limit, $offset, $id, null, null, null, 'created_at', 'DESC');
    $total_assets = $this->facade->getLoader()->countAssets($id, null, null, null);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createCategoryDetailResponse($category, $assets, $total_assets, $limit, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaCategoriesIdPatch(
    string $id,
    MediaCategoryRequest $media_category_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaCategoryResponse {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User || !$user->hasRole(User::ROLE_MEDIA_ADMIN)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $category = $this->facade->getLoader()->getCategoryById($id);
    if (!$category instanceof \App\DB\Entity\MediaLibrary\MediaCategory) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $this->facade->getProcessor()->updateCategory(
      $category,
      $media_category_request->getName(),
      $media_category_request->getDescription(),
      $media_category_request->getPriority()
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createCategoryResponse($category);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaCategoriesIdDelete(
    string $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User || !$user->hasRole(User::ROLE_MEDIA_ADMIN)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $category = $this->facade->getLoader()->getCategoryById($id);
    if (!$category instanceof \App\DB\Entity\MediaLibrary\MediaCategory) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $assets_count = $this->facade->getLoader()->countAssets($category->getId(), null, null, null);
    if ($assets_count > 0) {
      $responseCode = Response::HTTP_CONFLICT;

      return;
    }

    $this->facade->getProcessor()->deleteCategory($category);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function mediaAssetsGet(
    string $accept_language,
    int $limit,
    int $offset,
    ?string $category_id,
    ?string $file_type,
    ?string $flavor,
    ?string $search,
    string $sort_by,
    string $sort_order,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaAssetsResponse {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (!$this->checkIpRateLimit($ip, $this->mediaLibraryBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $this->mediaLibraryBurstLimiter, $ip);

    $db_file_type = $file_type ? $this->convertToDbFileType($file_type) : null;

    $assets = $this->facade->getLoader()->getAssets(
      $limit,
      $offset,
      $category_id,
      $db_file_type,
      $flavor,
      $search,
      $sort_by,
      $sort_order
    );

    $total = $this->facade->getLoader()->countAssets($category_id, $db_file_type, $flavor, $search);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createAssetsResponse($assets, $total, $limit, $offset);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaAssetsPost(
    UploadedFile $file,
    string $name,
    string $category_id,
    array $flavors,
    string $accept_language,
    ?string $description,
    ?string $author,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaAssetResponse {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User || !$user->hasRole(User::ROLE_MEDIA_ADMIN)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $category = $this->facade->getLoader()->getCategoryById($category_id);
    if (!$category instanceof \App\DB\Entity\MediaLibrary\MediaCategory) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $file_mime_type = $file->getMimeType() ?? '';
    $validation_errors = $this->facade->getRequestValidator()->validateFile($file, $file_mime_type);
    if ([] !== $validation_errors) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return null;
    }

    $file_type = str_starts_with($file_mime_type, 'audio/') ? DbMediaFileType::SOUND : DbMediaFileType::IMAGE;

    $flavor_entities = $this->loadFlavors($flavors);
    $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
    $extension = $extension ? strtolower($extension) : 'bin';

    $asset = $this->facade->getProcessor()->createAsset(
      $name,
      $description,
      $category,
      $flavor_entities,
      $file_type,
      $file,
      $extension,
      $author
    );

    $responseCode = Response::HTTP_CREATED;
    $response = $this->facade->getResponseManager()->createAssetResponse($asset);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaAssetsIdGet(
    string $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaAssetResponse {
    $asset = $this->facade->getLoader()->getAssetById($id);
    if (!$asset instanceof \App\DB\Entity\MediaLibrary\MediaAsset) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createAssetResponse($asset, $user);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaAssetsIdPatch(
    string $id,
    MediaAssetUpdateRequest $media_asset_update_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?MediaAssetResponse {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User || !$user->hasRole(User::ROLE_MEDIA_ADMIN)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $asset = $this->facade->getLoader()->getAssetById($id);
    if (!$asset instanceof \App\DB\Entity\MediaLibrary\MediaAsset) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $category = null;
    if ($media_asset_update_request->getCategoryId()) {
      $category = $this->facade->getLoader()->getCategoryById($media_asset_update_request->getCategoryId());
      if (!$category instanceof \App\DB\Entity\MediaLibrary\MediaCategory) {
        $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

        return null;
      }
    }

    $flavors = null;
    if ($media_asset_update_request->getFlavors()) {
      $flavors = $this->loadFlavors($media_asset_update_request->getFlavors());
    }

    $this->facade->getProcessor()->updateAsset(
      $asset,
      $media_asset_update_request->getName(),
      $media_asset_update_request->getDescription(),
      $category,
      $flavors,
      $media_asset_update_request->getAuthor(),
      $media_asset_update_request->isActive()
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createAssetResponse($asset);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function mediaAssetsIdDelete(
    string $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User || !$user->hasRole(User::ROLE_MEDIA_ADMIN)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $asset = $this->facade->getLoader()->getAssetById($id);
    if (!$asset instanceof \App\DB\Entity\MediaLibrary\MediaAsset) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $this->facade->getProcessor()->deleteAsset($asset);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  private function convertToDbFileType(string $apiType): ?DbMediaFileType
  {
    return match ($apiType) {
      'IMAGE' => DbMediaFileType::IMAGE,
      'SOUND' => DbMediaFileType::SOUND,
      default => null,
    };
  }

  /**
   * @param array<string> $flavor_names
   *
   * @return array<Flavor>
   */
  private function loadFlavors(array $flavor_names): array
  {
    $flavors = [];
    foreach ($flavor_names as $name) {
      $flavor = $this->flavor_repository->findOneBy(['name' => $name]);
      if ($flavor instanceof Flavor) {
        $flavors[] = $flavor;
      }
    }

    return $flavors;
  }
}
