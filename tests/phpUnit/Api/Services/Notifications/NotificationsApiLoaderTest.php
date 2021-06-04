<?php

namespace Tests\phpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\Notifications\NotificationsApiLoader;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsApiLoader
 */
final class NotificationsApiLoaderTest extends CatrowebTestCase
{
  /**
   * @var NotificationsApiLoader|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsApiLoader::class)
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
    $this->assertTrue(class_exists(NotificationsApiLoader::class));
    $this->assertInstanceOf(NotificationsApiLoader::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
