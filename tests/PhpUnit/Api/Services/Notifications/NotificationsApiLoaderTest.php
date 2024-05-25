<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use App\Api\Services\Notifications\NotificationsApiLoader;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Notifications\NotificationsApiLoader
 */
final class NotificationsApiLoaderTest extends DefaultTestCase
{
  protected MockObject|NotificationsApiLoader $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(NotificationsApiLoader::class)
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
    $this->assertTrue(class_exists(NotificationsApiLoader::class));
    $this->assertInstanceOf(NotificationsApiLoader::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiLoader::class, $this->object);
  }
}
