<?php

declare(strict_types=1);

namespace Tests\phpUnit\Api\Exceptions;

use App\Api\Exceptions\ApiException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversNothing
 */
final class ApiExceptionTest extends CatrowebTestCase
{
  /**
   * @var ApiException|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ApiException::class)
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
    $this->assertTrue(class_exists(ApiException::class));
    $this->assertInstanceOf(ApiException::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(Exception::class, $this->object);
  }
}
