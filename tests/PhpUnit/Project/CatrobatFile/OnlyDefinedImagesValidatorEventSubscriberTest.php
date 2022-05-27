<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\OnlyDefinedImagesValidatorEventSubscriber;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Project\CatrobatFile\OnlyDefinedImagesValidatorEventSubscriber
 */
class OnlyDefinedImagesValidatorEventSubscriberTest extends TestCase
{
  private OnlyDefinedImagesValidatorEventSubscriber $only_defined_images_validator;

  protected function setUp(): void
  {
    $this->only_defined_images_validator = new OnlyDefinedImagesValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(OnlyDefinedImagesValidatorEventSubscriber::class, $this->only_defined_images_validator);
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
    $this->expectException(InvalidCatrobatFileException::class);

    $this->only_defined_images_validator->validate($file);
  }
}
