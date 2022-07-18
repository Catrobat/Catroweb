<?php

namespace Project\Extension;

use App\Project\Extension\ProjectExtensionEventSubscriber;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 * @coversDefaultClass \App\Project\Extension\ProjectExtensionEventSubscriber
 */
class ProjectExtensionEventSubscriberTest extends DefaultTestCase
{
  protected ProjectExtensionEventSubscriber|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectExtensionEventSubscriber::class)
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
    $this->assertTrue(class_exists(ProjectExtensionEventSubscriber::class));
    $this->assertInstanceOf(ProjectExtensionEventSubscriber::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(EventSubscriberInterface::class, $this->object);
  }
}
