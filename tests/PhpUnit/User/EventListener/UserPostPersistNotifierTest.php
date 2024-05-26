<?php

declare(strict_types=1);

namespace Tests\PhpUnit\User\EventListener;

use App\System\Testing\PhpUnit\DefaultTestCase;
use App\User\EventListener\UserPostPersistNotifier;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class UserPostPersistNotifierTest extends DefaultTestCase
{
  protected MockObject|UserPostPersistNotifier $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserPostPersistNotifier::class)
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
    $this->assertTrue(class_exists(UserPostPersistNotifier::class));
    $this->assertInstanceOf(UserPostPersistNotifier::class, $this->object);
  }
}
