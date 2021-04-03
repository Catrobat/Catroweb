<?php

namespace Tests\phpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\MediaLibrary\MediaLibraryApiLoader;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryApiLoader
 */
final class MediaLibraryApiLoaderTest extends CatrowebTestCase
{
  /**
   * @var MediaLibraryApiLoader|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryApiLoader::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(MediaLibraryApiLoader::class));
    $this->assertInstanceOf(MediaLibraryApiLoader::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
