<?php

namespace Tests\phpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\Utility\UtilityApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Utility\UtilityApiProcessor
 */
final class UtilityApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var UtilityApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApiProcessor::class)
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
    $this->assertTrue(class_exists(UtilityApiProcessor::class));
    $this->assertInstanceOf(UtilityApiProcessor::class, $this->object);
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
