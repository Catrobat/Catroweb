<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Notifications\NotificationsApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsApiProcessor
 */
final class NotificationsApiProcessorTest extends DefaultTestCase
{
  protected MockObject|NotificationsApiProcessor $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsApiProcessor::class)
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
    $this->assertTrue(class_exists(NotificationsApiProcessor::class));
    $this->assertInstanceOf(NotificationsApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
