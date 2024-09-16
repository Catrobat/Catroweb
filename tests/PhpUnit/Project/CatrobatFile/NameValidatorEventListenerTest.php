<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\NameValidatorEventListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NameValidatorEventListener::class)]
class NameValidatorEventListenerTest extends TestCase
{
  private NameValidatorEventListener $name_validator;

  #[\Override]
  protected function setUp(): void
  {
    $this->name_validator = new NameValidatorEventListener();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NameValidatorEventListener::class, $this->name_validator);
  }

  /**
   * @throws Exception
   */
  public function testMakesSureTheGivenProgramNameIsValid(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('John Doe');
    $this->name_validator->validate($file);
  }

  /**
   * @throws Exception
   */
  public function testThrowsAnExceptionIfTheNameIsEmpty(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('');
    $this->expectException(InvalidCatrobatFileException::class);
    $this->name_validator->validate($file);
  }

  /**
   * @throws Exception
   */
  public function testThrowsAnExceptionIfTheNameIsTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $name = str_pad('a', 256, 'a');
    $file->expects($this->atLeastOnce())->method('getName')->willReturn($name);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->name_validator->validate($file);
  }
}
