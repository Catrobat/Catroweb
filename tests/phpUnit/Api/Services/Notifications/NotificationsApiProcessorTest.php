<?php

namespace Tests\phpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Notifications\NotificationsApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsApiProcessor
 */
final class NotificationsApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var NotificationsApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsApiProcessor::class)
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
    $this->assertTrue(class_exists(NotificationsApiProcessor::class));
    $this->assertInstanceOf(NotificationsApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
