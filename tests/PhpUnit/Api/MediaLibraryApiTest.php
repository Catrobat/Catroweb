<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\MediaLibraryApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\Api\Services\MediaLibrary\MediaLibraryApiLoader;
use App\Api\Services\MediaLibrary\MediaLibraryApiProcessor;
use App\Api\Services\MediaLibrary\MediaLibraryRequestValidator;
use App\Api\Services\MediaLibrary\MediaLibraryResponseManager;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\User\User;
use OpenAPI\Server\Model\MediaAssetResponse;
use OpenAPI\Server\Model\MediaAssetsResponse;
use OpenAPI\Server\Model\MediaAssetUpdateRequest;
use OpenAPI\Server\Model\MediaCategoriesResponse;
use OpenAPI\Server\Model\MediaCategoryDetailResponse;
use OpenAPI\Server\Model\MediaCategoryRequest;
use OpenAPI\Server\Model\MediaCategoryResponse;
use OpenAPI\Server\Model\MediaLibraryResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(MediaLibraryApi::class)]
final class MediaLibraryApiTest extends TestCase
{
  protected MediaLibraryApi $api;

  protected Stub&MediaLibraryApiFacade $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(MediaLibraryApiFacade::class);
    $flavor_repository = $this->createStub(\App\DB\EntityRepository\FlavorRepository::class);
    $no_limit = new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage());
    $request_stack = new \Symfony\Component\HttpFoundation\RequestStack();
    $this->api = new MediaLibraryApi($this->facade, $flavor_repository, $no_limit, $request_stack);
  }

  // ==================== mediaLibraryGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaLibraryGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategories')->willReturn([]);
    $loader->method('countCategories')->willReturn(0);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $library_response = $this->createStub(MediaLibraryResponse::class);
    $response_manager->method('createLibraryOverviewResponse')->willReturn($library_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->api->mediaLibraryGet('en', 10, 0, null, null, null, null, 5, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(MediaLibraryResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaLibraryGetWithSearch(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getAllCategories')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $library_response = $this->createStub(MediaLibraryResponse::class);
    $response_manager->method('createLibraryOverviewResponse')->willReturn($library_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->api->mediaLibraryGet('en', 10, 0, null, null, null, 'dog', 5, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(MediaLibraryResponse::class, $response);
  }

  // ==================== mediaCategoriesGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategories')->willReturn([]);
    $loader->method('countCategories')->willReturn(0);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $response_manager->method('sanitizeLocale')->willReturn('en');
    $response_manager->method('getCachedResponse')->willReturn(null);
    $categories_response = $this->createStub(MediaCategoriesResponse::class);
    $response_manager->method('createCategoriesResponse')->willReturn($categories_response);

    $this->facade->method('getLoader')->willReturn($loader);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->api->mediaCategoriesGet('en', 20, 0, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(MediaCategoriesResponse::class, $response);
  }

  // ==================== mediaCategoriesPost Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesPostUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $request = $this->createStub(MediaCategoryRequest::class);

    $response = $this->api->mediaCategoriesPost($request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesPostSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $processor = $this->createStub(MediaLibraryApiProcessor::class);
    $category = $this->createStub(MediaCategory::class);
    $processor->method('createCategory')->willReturn($category);
    $this->facade->method('getProcessor')->willReturn($processor);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $category_response = $this->createStub(MediaCategoryResponse::class);
    $response_manager->method('createCategoryResponse')->willReturn($category_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $request = $this->createStub(MediaCategoryRequest::class);
    $request->method('getName')->willReturn('media_library.category.figures');

    $response = $this->api->mediaCategoriesPost($request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CREATED, $response_code);
    $this->assertInstanceOf(MediaCategoryResponse::class, $response);
  }

  // ==================== mediaCategoriesIdGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategoryById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->api->mediaCategoriesIdGet('uuid', 'en', 20, 0, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesIdGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $category = $this->createStub(MediaCategory::class);
    $loader->method('getCategoryById')->willReturn($category);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $category_response = $this->createStub(MediaCategoryDetailResponse::class);
    $response_manager->method('createCategoryDetailResponse')->willReturn($category_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->api->mediaCategoriesIdGet('uuid', 'en', 20, 0, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(MediaCategoryDetailResponse::class, $response);
  }

  // ==================== mediaCategoriesIdPatch Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesIdPatchUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $request = $this->createStub(MediaCategoryRequest::class);

    $response = $this->api->mediaCategoriesIdPatch('uuid', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesIdPatchNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategoryById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $request = $this->createStub(MediaCategoryRequest::class);

    $response = $this->api->mediaCategoriesIdPatch('uuid', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  // ==================== mediaCategoriesIdDelete Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesIdDeleteUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->api->mediaCategoriesIdDelete('uuid', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaCategoriesIdDeleteNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategoryById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->api->mediaCategoriesIdDelete('uuid', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
  }

  // ==================== mediaAssetsGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getAssets')->willReturn([]);
    $loader->method('countAssets')->willReturn(0);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $assets_response = $this->createStub(MediaAssetsResponse::class);
    $response_manager->method('createAssetsResponse')->willReturn($assets_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->api->mediaAssetsGet('en', 20, 0, null, null, null, null, null, 'name', 'asc', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(MediaAssetsResponse::class, $response);
  }

  // ==================== mediaAssetsIdGet Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getAssetById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->api->mediaAssetsIdGet('uuid', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsIdGetSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $asset = $this->createStub(MediaAsset::class);
    $loader->method('getAssetById')->willReturn($asset);
    $this->facade->method('getLoader')->willReturn($loader);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $asset_response = $this->createStub(MediaAssetResponse::class);
    $response_manager->method('createAssetResponse')->willReturn($asset_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $response = $this->api->mediaAssetsIdGet('uuid', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(MediaAssetResponse::class, $response);
  }

  // ==================== mediaAssetsIdPatch Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsIdPatchUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $request = $this->createStub(MediaAssetUpdateRequest::class);

    $response = $this->api->mediaAssetsIdPatch('uuid', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsIdPatchNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getAssetById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $request = $this->createStub(MediaAssetUpdateRequest::class);

    $response = $this->api->mediaAssetsIdPatch('uuid', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  // ==================== mediaAssetsIdDelete Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsIdDeleteUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $this->api->mediaAssetsIdDelete('uuid', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsIdDeleteNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getAssetById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->api->mediaAssetsIdDelete('uuid', 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
  }

  // ==================== mediaAssetsPost Tests ====================

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsPostUnauthorized(): void
  {
    $response_code = 200;
    $response_headers = [];

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $file = $this->createStub(UploadedFile::class);

    $response = $this->api->mediaAssetsPost($file, 'Dog Image', 'uuid', ['pocketcode'], 'en', null, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsPostCategoryNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategoryById')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $file = $this->createStub(UploadedFile::class);

    $response = $this->api->mediaAssetsPost($file, 'Dog Image', 'uuid', ['pocketcode'], 'en', null, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsPostValidationError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $category = $this->createStub(MediaCategory::class);
    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategoryById')->willReturn($category);
    $this->facade->method('getLoader')->willReturn($loader);

    $validator = $this->createStub(MediaLibraryRequestValidator::class);
    $validator->method('validateFile')->willReturn(['error']);
    $this->facade->method('getRequestValidator')->willReturn($validator);

    $file = $this->createStub(UploadedFile::class);
    $file->method('getMimeType')->willReturn('image/png');

    $response = $this->api->mediaAssetsPost($file, 'Dog Image', 'uuid', ['pocketcode'], 'en', null, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaAssetsPostSuccess(): void
  {
    $response_code = 200;
    $response_headers = [];

    $user = $this->createStub(User::class);
    $user->method('hasRole')->willReturn(true);
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);
    $this->facade->method('getAuthenticationManager')->willReturn($authentication_manager);

    $category = $this->createStub(MediaCategory::class);
    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getCategoryById')->willReturn($category);
    $this->facade->method('getLoader')->willReturn($loader);

    $validator = $this->createStub(MediaLibraryRequestValidator::class);
    $validator->method('validateFile')->willReturn([]);
    $this->facade->method('getRequestValidator')->willReturn($validator);

    $processor = $this->createStub(MediaLibraryApiProcessor::class);
    $asset = $this->createStub(MediaAsset::class);
    $processor->method('createAsset')->willReturn($asset);
    $this->facade->method('getProcessor')->willReturn($processor);

    $response_manager = $this->createStub(MediaLibraryResponseManager::class);
    $asset_response = $this->createStub(MediaAssetResponse::class);
    $response_manager->method('createAssetResponse')->willReturn($asset_response);
    $this->facade->method('getResponseManager')->willReturn($response_manager);

    $file = $this->createStub(UploadedFile::class);
    $file->method('getMimeType')->willReturn('image/png');
    $file->method('getClientOriginalExtension')->willReturn('png');

    $response = $this->api->mediaAssetsPost($file, 'Dog Image', 'uuid', ['pocketcode'], 'en', null, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_CREATED, $response_code);
    $this->assertInstanceOf(MediaAssetResponse::class, $response);
  }
}
