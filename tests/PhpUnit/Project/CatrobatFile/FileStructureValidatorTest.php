<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\FileStructureValidatorEventSubscriber;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Project\CatrobatFile\FileStructureValidatorEventSubscriber
 */
class FileStructureValidatorTest extends TestCase
{
  private FileStructureValidatorEventSubscriber $file_structure_validator;

  protected function setUp(): void
  {
    $this->file_structure_validator = new FileStructureValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(FileStructureValidatorEventSubscriber::class, $this->file_structure_validator);
  }

  public function testMakesSureTheProgramHasAValidFileStructure(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $file->expects($this->atLeastOnce())->method('getPath')
      ->willReturn(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base')
      ;
    $this->file_structure_validator->validate($file);
  }
}
