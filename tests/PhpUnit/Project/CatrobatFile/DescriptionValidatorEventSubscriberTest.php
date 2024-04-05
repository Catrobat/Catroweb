<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\DescriptionValidatorEventSubscriber;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatFile\DescriptionValidatorEventSubscriber
 */
class DescriptionValidatorEventSubscriberTest extends TestCase
{
  private DescriptionValidatorEventSubscriber $description_validator;

  protected function setUp(): void
  {
    $this->description_validator = new DescriptionValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(DescriptionValidatorEventSubscriber::class, $this->description_validator);
  }

  public function testThrowsAnExceptionIfTheDescriptionIsTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $description = str_pad('a', 10_001, 'a');
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn($description);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->description_validator->validate($file);
  }

  public function testThrowsNothingIfANormalDescriptionIsValidated(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn('Hello Text.');
    $this->description_validator->validate($file);
  }
}
