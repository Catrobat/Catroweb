<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\NameValidatorEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NameValidatorEventSubscriber::class)]
class NameValidatorEventSubscriberTest extends TestCase
{
  private NameValidatorEventSubscriber $name_validator;

  #[\Override]
  protected function setUp(): void
  {
    $this->name_validator = new NameValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(NameValidatorEventSubscriber::class, $this->name_validator);
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
