<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\ProgramExtensionListener;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Entity\Extension;
use App\Entity\Program;
use App\Repository\ExtensionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\ProgramExtensionListener
 */
class ProgramExtensionListenerTest extends TestCase
{
  public ExtractedCatrobatFile $extracted_catrobat_file_with_extensions;

  public ExtractedCatrobatFile $extracted_catrobat_file_without_extensions;

  private ProgramExtensionListener $program_extension_listener;

  /**
   * @var Extension|MockObject
   */
  private $extension;

  protected function setUp(): void
  {
    $this->extension = $this->createMock(Extension::class);
    $this->extension->expects($this->any())->method('getPrefix')->willReturn('PHIRO');

    $extension_repository = $this->createMock(ExtensionRepository::class);
    $extension_repository->expects($this->any())->method('findAll')->willReturn([$this->extension]);

    $this->program_extension_listener = new ProgramExtensionListener($extension_repository);

    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_extensions/', RefreshTestEnvHook::$CACHE_DIR.'program_with_extensions/');
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', RefreshTestEnvHook::$CACHE_DIR.'base/');

    $this->extracted_catrobat_file_without_extensions = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '', '');

    $this->extracted_catrobat_file_with_extensions = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'program_with_extensions/', '', '');
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProgramExtensionListener::class, $this->program_extension_listener);
  }

  public function testFlagsAProgramIfExtensionBricksAreUsed(): void
  {
    $program = $this->createMock(Program::class);
    $program->expects($this->atLeastOnce())->method('addExtension')->with($this->extension);
    $this->program_extension_listener->checkExtension($this->extracted_catrobat_file_with_extensions, $program);
  }

  public function testDoesNotFlagsAProgramIfNoExtensionBricksAreUsed(): void
  {
    $program = $this->createMock(Program::class);
    $program->expects($this->never())->method('addExtension')->with($this->extension);
    $this->program_extension_listener->checkExtension($this->extracted_catrobat_file_without_extensions, $program);
  }
}
