<?php

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\User\UserResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\User\UserResponseManager
 */
final class UserResponseManagerTest extends DefaultTestCase
{
  protected MockObject|UserResponseManager $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserResponseManager::class)
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
    $this->assertTrue(class_exists(UserResponseManager::class));
    $this->assertInstanceOf(UserResponseManager::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractResponseManager::class, $this->object);
  }
}
