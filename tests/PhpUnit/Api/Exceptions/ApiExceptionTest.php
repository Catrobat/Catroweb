<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Exceptions;

use App\Api\Exceptions\ApiException;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
final class ApiExceptionTest extends DefaultTestCase
{
  protected ApiException|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ApiException::class)
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
    $this->assertTrue(class_exists(ApiException::class));
    $this->assertInstanceOf(ApiException::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(\Exception::class, $this->object);
  }
}
