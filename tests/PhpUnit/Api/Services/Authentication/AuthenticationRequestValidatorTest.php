<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationRequestValidator;
use App\Api\Services\Base\AbstractRequestValidator;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\Authentication\AuthenticationRequestValidator
 */
final class AuthenticationRequestValidatorTest extends DefaultTestCase
{
  protected AuthenticationRequestValidator|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(AuthenticationRequestValidator::class)
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
    $this->assertTrue(class_exists(AuthenticationRequestValidator::class));
    $this->assertInstanceOf(AuthenticationRequestValidator::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractRequestValidator::class, $this->object);
  }
}
