<?php

namespace Tests\phpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractApiLoader;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Base\AbstractApiLoader
 */
final class AbstractApiLoaderTest extends CatrowebTestCase
{
  /**
   * @var AbstractApiLoader|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractApiLoader::class)
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
    $this->assertTrue(class_exists(AbstractApiLoader::class));
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
