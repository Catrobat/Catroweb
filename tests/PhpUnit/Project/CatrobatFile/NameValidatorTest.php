<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\NameValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers  \App\Project\CatrobatFile\NameValidator
 */
class NameValidatorTest extends TestCase
{
  private NameValidator $name_validator;

  protected function setUp(): void
  {
    $this->name_validator = new NameValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NameValidator::class, $this->name_validator);
  }

  public function testMakesSureTheGivenProgramNameIsValid(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('John Doe');
    $this->name_validator->validate($file);
  }

  public function testThrowsAnExceptionIfTheNameIsEmpty(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getName')->willReturn('');
    $this->expectException(InvalidCatrobatFileException::class);
    $this->name_validator->validate($file);
  }

  public function testThrowsAnExceptionIfTheNameIsTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $name = str_pad('a', 201, 'a');
    $file->expects($this->atLeastOnce())->method('getName')->willReturn($name);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->name_validator->validate($file);
  }
}
