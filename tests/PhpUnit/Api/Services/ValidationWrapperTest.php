<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services;

use App\Api\Services\ValidationWrapper;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\Services\ValidationWrapper
 */
final class ValidationWrapperTest extends DefaultTestCase
{
  protected MockObject|ValidationWrapper $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ValidationWrapper::class)
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
    $this->assertTrue(class_exists(ValidationWrapper::class));
    $this->assertInstanceOf(ValidationWrapper::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::addError
   */
  public function testAddError(): void
  {
    $this->object->addError('Error 1');
    $this->assertEquals(['Error 1'], $this->object->getErrors());
    $this->object->addError('Error 2');
    $this->assertEquals(['Error 1', 'Error 2'], $this->object->getErrors());
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::addError
   */
  public function testAddErrorWithKey(): void
  {
    $this->assertFalse($this->object->hasError());
    $this->object->addError('Error 1', 'key');
    $this->assertTrue($this->object->hasError());
    $this->assertEquals(['key' => 'Error 1'], $this->object->getErrors());
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::hasError
   */
  public function testHasError(): void
  {
    $this->assertFalse($this->object->hasError());
    $this->object->addError('Error 1');
    $this->assertTrue($this->object->hasError());
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::clear
   */
  public function testClear(): void
  {
    $this->object->addError('Error 1');
    $this->assertTrue($this->object->hasError());
    $this->object->clear();
    $this->assertFalse($this->object->hasError());
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::getError
   */
  public function testGetErrorEmpty(): void
  {
    $this->assertEquals('', $this->object->getError());
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::getError
   */
  public function testGetErrorNoKey(): void
  {
    $this->object->addError('Error 1');
    $this->object->addError('Error 2');
    $this->assertEquals('Error 1', $this->object->getError());
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\Services\ValidationWrapper::getError
   */
  public function testGetErrorKey(): void
  {
    $this->object->addError('Error 1', 'key 1');
    $this->object->addError('Error 2', 'key 2');
    $this->assertEquals('Error 2', $this->object->getError('key 2'));
    $this->assertEquals('Error 1', $this->object->getError('key 1'));
  }
}
