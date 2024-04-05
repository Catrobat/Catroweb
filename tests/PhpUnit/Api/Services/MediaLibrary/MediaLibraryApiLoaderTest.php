<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\MediaLibrary\MediaLibraryApiLoader;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryApiLoader
 */
final class MediaLibraryApiLoaderTest extends DefaultTestCase
{
  protected MediaLibraryApiLoader|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryApiLoader::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(MediaLibraryApiLoader::class));
    $this->assertInstanceOf(MediaLibraryApiLoader::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
