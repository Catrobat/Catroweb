<?php

namespace Tests\phpUnit\Api\Services\Utility;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Utility\UtilityRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Utility\UtilityRequestValidator
 */
final class UtilityRequestValidatorTest extends CatrowebTestCase
{
  /**
   * @var UtilityRequestValidator|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityRequestValidator::class)
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
    $this->assertTrue(class_exists(UtilityRequestValidator::class));
    $this->assertInstanceOf(UtilityRequestValidator::class, $this->object);
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
