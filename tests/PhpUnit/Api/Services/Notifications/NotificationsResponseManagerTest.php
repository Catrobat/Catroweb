<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Notifications\NotificationsResponseManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsResponseManager
 */
final class NotificationsResponseManagerTest extends DefaultTestCase
{
  protected MockObject|NotificationsResponseManager $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsResponseManager::class)
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
    $this->assertTrue(class_exists(NotificationsResponseManager::class));
    $this->assertInstanceOf(NotificationsResponseManager::class, $this->object);
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
