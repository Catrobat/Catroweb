<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Exceptions;

use App\Api\Exceptions\ApiException;
use App\Api\Exceptions\ApiVersionNotSupportedException;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
final class ApiVersionNotSupportedExceptionTest extends DefaultTestCase
{
  protected ApiVersionNotSupportedException|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ApiVersionNotSupportedException::class)
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
    $this->assertTrue(class_exists(ApiVersionNotSupportedException::class));
    $this->assertInstanceOf(ApiVersionNotSupportedException::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(ApiException::class, $this->object);
  }
}
