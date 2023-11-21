<?php

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Notifications\NotificationsRequestValidator;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsRequestValidator
 */
final class NotificationsRequestValidatorTest extends DefaultTestCase
{
  protected MockObject|NotificationsRequestValidator $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsRequestValidator::class)
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
    $this->assertTrue(class_exists(NotificationsRequestValidator::class));
    $this->assertInstanceOf(NotificationsRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }
}
