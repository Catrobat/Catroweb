<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\MediaLibraryApi;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\Api\Services\MediaLibrary\MediaLibraryApiLoader;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\System\Testing\PhpUnit\DefaultTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFileResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\MediaLibraryApi
 */
final class MediaLibraryApiTest extends DefaultTestCase
{
  protected MediaLibraryApi|MockObject $object;

  protected MediaLibraryApiFacade|MockObject $facade;

  /**
   * @throws \ReflectionException
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->createMock(MediaLibraryApiFacade::class);
    $this->mockProperty(MediaLibraryApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(MediaLibraryApi::class));
    $this->assertInstanceOf(MediaLibraryApi::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiController::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(MediaLibraryApiInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new MediaLibraryApi($this->facade);
    $this->assertInstanceOf(MediaLibraryApi::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\MediaLibraryApi::mediaFilesSearchGet
   */
  public function testMediaFilesSearchGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(MediaLibraryApiLoader::class);
    $loader->method('searchMediaLibraryFiles')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->object->mediaFilesSearchGet('query', 20, 0, '', '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\MediaLibraryApi::mediaPackageNameGet
   */
  public function testMediaPackageNameGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageByName')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->mediaPackageNameGet('name', 20, 0, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\MediaLibraryApi::mediaPackageNameGet
   */
  public function testMediaPackageNameGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(MediaLibraryApiLoader::class);
    $mediaPackage = $this->createMock(MediaPackage::class);
    $mediaPackage->method('getCategories')->willReturn(new ArrayCollection());
    $loader->method('getMediaPackageByName')->willReturn($mediaPackage);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->mediaPackageNameGet('name', 20, 0, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertIsArray($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\MediaLibraryApi::mediaFileIdGet
   */
  public function testMediaFileIdGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageFileByID')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->mediaFileIdGet(1, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\MediaLibraryApi::mediaFileIdGet
   */
  public function testMediaFileIdGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageFileByID')->willReturn($this->createMock(MediaPackageFile::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->mediaFileIdGet(1, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(MediaFileResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\MediaLibraryApi::mediaFilesGet
   */
  public function testMediaFilesGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(MediaLibraryApiLoader::class);
    $loader->method('getMediaPackageFiles')->willReturn([]);
    $this->facade->method('getLoader')->willReturn($loader);

    $this->object->mediaFilesGet(20, 0, '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);
  }
}
