<?php

namespace Tests\phpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationResponseManager;
use App\Api\Services\Base\AbstractResponseManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationResponseManager
 */
final class AuthenticationResponseManagerTest extends CatrowebTestCase
{
  /**
   * @var AuthenticationResponseManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationResponseManager::class)
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
    $this->assertTrue(class_exists(AuthenticationResponseManager::class));
    $this->assertInstanceOf(AuthenticationResponseManager::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }
}
