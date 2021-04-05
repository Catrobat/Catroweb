<?php

namespace Tests\phpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Notifications\NotificationsRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsRequestValidator
 */
final class NotificationsRequestValidatorTest extends CatrowebTestCase
{
  /**
   * @var NotificationsRequestValidator|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsRequestValidator::class)
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
    $this->assertTrue(class_exists(NotificationsRequestValidator::class));
    $this->assertInstanceOf(NotificationsRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }
}
