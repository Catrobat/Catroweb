<?php

declare(strict_types=1);

namespace Tests\phpUnit\Api\Exceptions;

use App\Api\Exceptions\ApiException;
use App\Api\Exceptions\ApiVersionNotSupportedException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
final class ApiVersionNotSupportedExceptionTest extends CatrowebTestCase
{
  /**
   * @var ApiVersionNotSupportedException|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ApiVersionNotSupportedException::class)
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
    $this->assertTrue(class_exists(ApiVersionNotSupportedException::class));
    $this->assertInstanceOf(ApiVersionNotSupportedException::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(ApiException::class, $this->object);
  }
}
