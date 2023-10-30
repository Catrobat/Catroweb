<?php

namespace Tests\PhpUnit\User\EventListener;

use App\System\Testing\PhpUnit\DefaultTestCase;
use App\User\EventListener\UserPostUpdateNotifier;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class UserPostUpdateNotifierTest extends DefaultTestCase
{
  protected MockObject|UserPostUpdateNotifier $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserPostUpdateNotifier::class)
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
    $this->assertTrue(class_exists(UserPostUpdateNotifier::class));
    $this->assertInstanceOf(UserPostUpdateNotifier::class, $this->object);
  }
}
