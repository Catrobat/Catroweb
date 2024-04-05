<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiFacade;
use App\Api\Services\Notifications\NotificationsApiFacade;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsApiFacade
 */
final class NotificationsApiFacadeTest extends DefaultTestCase
{
  protected MockObject|NotificationsApiFacade $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsApiFacade::class)
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
    $this->assertTrue(class_exists(NotificationsApiFacade::class));
    $this->assertInstanceOf(NotificationsApiFacade::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiFacade::class, $this->object);
  }
}
