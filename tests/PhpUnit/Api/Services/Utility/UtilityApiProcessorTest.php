<?php

namespace Tests\PhpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Utility\UtilityApiProcessor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Utility\UtilityApiProcessor
 */
final class UtilityApiProcessorTest extends DefaultTestCase
{
  protected MockObject|UtilityApiProcessor $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApiProcessor::class)
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
    $this->assertTrue(class_exists(UtilityApiProcessor::class));
    $this->assertInstanceOf(UtilityApiProcessor::class, $this->object);
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
