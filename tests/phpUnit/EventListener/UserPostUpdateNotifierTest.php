<?php

namespace Tests\phpUnit\EventListener;

use App\EventListener\UserPostUpdateNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class UserPostUpdateNotifierTest extends CatrowebTestCase
{
  /**
   * @var UserPostUpdateNotifier|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserPostUpdateNotifier::class)
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
    $this->assertTrue(class_exists(UserPostUpdateNotifier::class));
    $this->assertInstanceOf(UserPostUpdateNotifier::class, $this->object);
  }
}
