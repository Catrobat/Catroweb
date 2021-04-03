<?php

namespace Tests\phpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\User\UserResponseManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\User\UserResponseManager
 */
final class UserResponseManagerTest extends CatrowebTestCase
{
  /**
   * @var UserResponseManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserResponseManager::class)
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
    $this->assertTrue(class_exists(UserResponseManager::class));
    $this->assertInstanceOf(UserResponseManager::class, $this->object);
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
