<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\User\UserRequestValidator;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\User\UserRequestValidator
 */
final class UserRequestValidatorTest extends DefaultTestCase
{
  protected MockObject|UserRequestValidator $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UserRequestValidator::class)
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
    $this->assertTrue(class_exists(UserRequestValidator::class));
    $this->assertInstanceOf(UserRequestValidator::class, $this->object);
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
