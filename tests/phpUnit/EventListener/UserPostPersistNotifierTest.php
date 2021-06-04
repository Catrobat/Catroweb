<?php

namespace Tests\phpUnit\EventListener;

use App\EventListener\UserPostPersistNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
class UserPostPersistNotifierTest extends CatrowebTestCase
{
  /**
   * @var UserPostPersistNotifier|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserPostPersistNotifier::class)
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
    $this->assertTrue(class_exists(UserPostPersistNotifier::class));
    $this->assertInstanceOf(UserPostPersistNotifier::class, $this->object);
  }
}
