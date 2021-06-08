<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\DescriptionTooLongException;
use App\Catrobat\Listeners\DescriptionValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\Listeners\DescriptionValidator
 */
class DescriptionValidatorTest extends TestCase
{
  private DescriptionValidator $description_validator;

  protected function setUp(): void
  {
    $this->description_validator = new DescriptionValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(DescriptionValidator::class, $this->description_validator);
  }

  public function testThrowsAnExceptionIfTheDescriptionIsTooLong(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $description = str_pad('a', 10_001, 'a');
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn($description);
    $this->expectException(DescriptionTooLongException::class);
    $this->description_validator->validate($file);
  }

  public function testThrowsNothingIfANormalDescriptionIsValidated(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getDescription')->willReturn('Hello Text.');
    $this->description_validator->validate($file);
  }
}
