<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\MediaLibraryApi;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\Api\Services\MediaLibrary\MediaLibraryApiLoader;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\System\Testing\PhpUnit\DefaultTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use OpenAPI\Server\Model\MediaFileResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(MediaLibraryApi::class)]
final class MediaLibraryApiTest extends DefaultTestCase
{
  protected MediaLibraryApi $media_library_api;

  protected MediaLibraryApiFacade|Stub $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createStub(MediaLibraryApiFacade::class);
    $this->media_library_api = new MediaLibraryApi($this->facade);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaFilesSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('searchMediaLibraryFiles')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->media_library_api->mediaFilesSearchGet('query', 20, 0, '', '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaPackageNameGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageByName')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->media_library_api->mediaPackageNameGet('name', 20, 0, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaPackageNameGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $mediaPackage = $this->createStub(MediaPackage::class);
    $mediaPackage->method('getCategories')->willReturn(new ArrayCollection());
    $loader->method('getMediaPackageByName')->willReturn($mediaPackage);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->media_library_api->mediaPackageNameGet('name', 20, 0, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertIsArray($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaFileIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageFileByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->media_library_api->mediaFileIdGet(1, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaFileIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageFileByID')->willReturn($this->createStub(MediaPackageFile::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->media_library_api->mediaFileIdGet(1, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(MediaFileResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testMediaFilesGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createStub(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageFiles')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->media_library_api->mediaFilesGet(20, 0, '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }
}
