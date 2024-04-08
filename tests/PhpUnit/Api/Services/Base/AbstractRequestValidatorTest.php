<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\Base\TranslatorAwareInterface;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Base\AbstractRequestValidator
 */
final class AbstractRequestValidatorTest extends DefaultTestCase
{
  protected AbstractRequestValidator|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AbstractRequestValidator::class)
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
    $this->assertTrue(class_exists(AbstractRequestValidator::class));
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(TranslatorAwareInterface::class, $this->object);
  }
}
