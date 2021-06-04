<?php

namespace Tests\phpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Notifications\NotificationsResponseManager;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsResponseManager
 */
final class NotificationsResponseManagerTest extends CatrowebTestCase
{
  /**
   * @var NotificationsResponseManager|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsResponseManager::class)
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
    $this->assertTrue(class_exists(NotificationsResponseManager::class));
    $this->assertInstanceOf(NotificationsResponseManager::class, $this->object);
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
