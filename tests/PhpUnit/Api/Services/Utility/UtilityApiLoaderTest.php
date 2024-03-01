<?php

namespace Tests\PhpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\Utility\UtilityApiLoader;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Utility\UtilityApiLoader
 */
final class UtilityApiLoaderTest extends DefaultTestCase
{
  protected MockObject|UtilityApiLoader $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApiLoader::class)
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
    $this->assertTrue(class_exists(UtilityApiLoader::class));
    $this->assertInstanceOf(UtilityApiLoader::class, $this->object);
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
