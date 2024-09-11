<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services;

use App\Api\Services\ValidationWrapper;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
#[CoversClass(ValidationWrapper::class)]
final class ValidationWrapperTest extends DefaultTestCase
{
  protected MockObject|ValidationWrapper $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ValidationWrapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMock()
    ;
  }

  #[Group('unit')]
  public function testAddError(): void
  {
    $this->object->addError('Error 1');
    $this->assertEquals(['Error 1'], $this->object->getErrors());
    $this->object->addError('Error 2');
    $this->assertEquals(['Error 1', 'Error 2'], $this->object->getErrors());
  }

  #[Group('unit')]
  public function testAddErrorWithKey(): void
  {
    $this->assertFalse($this->object->hasError());
    $this->object->addError('Error 1', 'key');
    $this->assertTrue($this->object->hasError());
    $this->assertEquals(['key' => 'Error 1'], $this->object->getErrors());
  }

  #[Group('unit')]
  public function testHasError(): void
  {
    $this->assertFalse($this->object->hasError());
    $this->object->addError('Error 1');
    $this->assertTrue($this->object->hasError());
  }

  #[Group('unit')]
  public function testClear(): void
  {
    $this->object->addError('Error 1');
    $this->assertTrue($this->object->hasError());
    $this->object->clear();
    $this->assertFalse($this->object->hasError());
  }

  #[Group('unit')]
  public function testGetErrorEmpty(): void
  {
    $this->assertEquals('', $this->object->getError());
  }

  #[Group('unit')]
  public function testGetErrorNoKey(): void
  {
    $this->object->addError('Error 1');
    $this->object->addError('Error 2');
    $this->assertEquals('Error 1', $this->object->getError());
  }

  #[Group('unit')]
  public function testGetErrorKey(): void
  {
    $this->object->addError('Error 1', 'key 1');
    $this->object->addError('Error 2', 'key 2');
    $this->assertEquals('Error 2', $this->object->getError('key 2'));
    $this->assertEquals('Error 1', $this->object->getError('key 1'));
  }
}
