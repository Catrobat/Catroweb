<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\FileStructureValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;
use Tests\phpUnit\Hook\ClearCacheHook;

/**
 * @internal
 * @coversNothing
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
      ->willReturn(ClearCacheHook::$GENERATED_FIXTURES_DIR.'base')
      ;
    $this->file_structure_validator->validate($file);
  }
}
