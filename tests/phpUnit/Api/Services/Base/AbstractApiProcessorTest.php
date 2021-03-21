<?php

namespace Tests\phpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Base\AbstractApiProcessor
 */
final class AbstractApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var AbstractApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractApiProcessor::class)
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
    $this->assertTrue(class_exists(AbstractApiProcessor::class));
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
