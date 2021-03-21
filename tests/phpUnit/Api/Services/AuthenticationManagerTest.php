<?php

declare(strict_types=1);

namespace Tests\phpUnit\Api\Services;

use App\Api\Services\AuthenticationManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\AuthenticationManager
 */
final class AuthenticationManagerTest extends CatrowebTestCase
{
  /**
   * @var AuthenticationManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationManager::class)
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
    $this->assertTrue(class_exists(AuthenticationManager::class));
    $this->assertInstanceOf(AuthenticationManager::class, $this->object);
  }
}
