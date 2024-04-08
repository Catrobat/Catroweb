<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Base\AbstractApiProcessor
 */
final class AbstractApiProcessorTest extends DefaultTestCase
{
  protected AbstractApiProcessor|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractApiProcessor::class)
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
    $this->assertTrue(class_exists(AbstractApiProcessor::class));
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
