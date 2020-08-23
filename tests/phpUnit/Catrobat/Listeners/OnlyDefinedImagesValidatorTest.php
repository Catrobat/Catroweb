<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Exceptions\Upload\MissingImageException;
use App\Catrobat\Listeners\OnlyDefinedImagesValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\OnlyDefinedImagesValidator
 */
class OnlyDefinedImagesValidatorTest extends TestCase
{
  private OnlyDefinedImagesValidator $only_defined_images_validator;

  protected function setUp(): void
  {
    $this->only_defined_images_validator = new OnlyDefinedImagesValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(OnlyDefinedImagesValidator::class, $this->only_defined_images_validator);
  }

  public function testMakesSureOnlyImagesDefinedInTheXmlAreInTheImageDirectory(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getPath')
      ->willReturn(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base')
      ;
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')
      ->willReturn(simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/code.xml'))
      ;
    $this->only_defined_images_validator->validate($file);
  }

  public function testThrowsAnExceptionIfThereIsAnImageNotSpecifiedInXml(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getPath')
      ->willReturn(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_extra_image')
    ;
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')
      ->willReturn(simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_extra_image/code.xml'))
    ;
    $this->expectException(InvalidCatrobatFileException::class);
    $this->only_defined_images_validator->validate($file);
  }

  public function testThrowsAnExceptionIfAImageIsMissing(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);

    $file->expects($this->atLeastOnce())->method('getPath')
      ->willReturn(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_missing_image')
    ;
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')
      ->willReturn(simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_missing_image/code.xml'))
    ;
    $this->expectException(MissingImageException::class);

    $this->only_defined_images_validator->validate($file);
  }
}
