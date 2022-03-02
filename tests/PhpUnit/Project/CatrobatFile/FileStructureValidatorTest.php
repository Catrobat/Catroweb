<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\FileStructureValidator;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Project\CatrobatFile\FileStructureValidator
 */
class FileStructureValidatorTest extends TestCase
{
  private FileStructureValidator $file_structure_validator;

  protected function setUp(): void
  {
    $this->file_structure_validator = new FileStructureValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(FileStructureValidator::class, $this->file_structure_validator);
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
