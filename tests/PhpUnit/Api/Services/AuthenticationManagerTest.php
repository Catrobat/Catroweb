<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services;

use App\Api\Services\AuthenticationManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\AuthenticationManager
 */
final class AuthenticationManagerTest extends DefaultTestCase
{
  protected AuthenticationManager|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationManager::class)
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
    $this->assertTrue(class_exists(AuthenticationManager::class));
    $this->assertInstanceOf(AuthenticationManager::class, $this->object);
  }
}
