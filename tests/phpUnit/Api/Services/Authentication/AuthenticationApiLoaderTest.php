<?php

namespace Tests\phpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationApiLoader;
use App\Api\Services\Base\AbstractApiLoader;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationApiLoader
 */
final class AuthenticationApiLoaderTest extends CatrowebTestCase
{
  /**
   * @var AuthenticationApiLoader|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationApiLoader::class)
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
    $this->assertTrue(class_exists(AuthenticationApiLoader::class));
    $this->assertInstanceOf(AuthenticationApiLoader::class, $this->object);
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
