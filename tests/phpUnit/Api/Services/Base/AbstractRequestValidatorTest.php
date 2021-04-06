<?php

namespace Tests\phpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Base\TranslatorAwareInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\Base\AbstractRequestValidator
 */
final class AbstractRequestValidatorTest extends CatrowebTestCase
{
  /**
   * @var AbstractRequestValidator|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractRequestValidator::class)
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
    $this->assertTrue(class_exists(AbstractRequestValidator::class));
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(TranslatorAwareInterface::class, $this->object);
  }
}
