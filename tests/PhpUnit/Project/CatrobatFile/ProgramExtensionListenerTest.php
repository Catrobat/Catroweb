<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ProgramExtensionListener;
use App\System\Testing\PhpUnit\DefaultTestCase;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 * @coversDefaultClass \App\Project\CatrobatFile\ProgramExtensionListener
 */
class ProgramExtensionListenerTest extends DefaultTestCase
{
  protected ExtractedCatrobatFile $extracted_catrobat_file_with_extensions;
  protected ExtractedCatrobatFile $extracted_catrobat_file_without_extensions;

  protected ProgramExtensionListener|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProgramExtensionListener::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->setUpCatrobatTestFiles();
  }

  /**
   * @group unit
   * @small
   * @covers ::addExtensions
   */
  public function testAddExtensions(): void
  {
    $program = $this
      ->getMockBuilder(Program::class)
      ->onlyMethods(['removeAllExtensions'])
      ->getMockForAbstractClass()
    ;

    $this->object = $this->getMockBuilder(ProgramExtensionListener::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['addArduinoExtensions', 'addPhiroExtensions', 'addEmbroideryExtensions', 'addMindstormsExtensions', 'getExtension', 'saveProject'])
      ->getMockForAbstractClass()
    ;

    $program->expects($this->once())->method('removeAllExtensions');
    $this->object->expects($this->once())->method('addArduinoExtensions');
    $this->object->expects($this->once())->method('addPhiroExtensions');
    $this->object->expects($this->once())->method('addEmbroideryExtensions');
    $this->object->expects($this->once())->method('addMindstormsExtensions');
    $this->object->expects($this->once())->method('saveProject');

    $this->object->addExtensions($this->extracted_catrobat_file_with_extensions, $program);
  }

  /**
   * @group unit
   * @small
   * @covers ::isAnEmbroideryProject
   * @dataProvider dataProviderIsAnEmbroideryProject
   */
  public function testIsAnEmbroideryProject(string $code_xml, bool $expected): void
  {
    $this->assertEquals(
          $expected,
          $this->invokeMethod($this->object, 'isAnEmbroideryProject', [$code_xml])
      );
  }

  public function dataProviderIsAnEmbroideryProject(): array
  {
    return [
      'invalid' => ['bla bla <brick type="NoStitchBrick" bla bla', false],
      'valid' => ['bla bla <brick type="StitchBrick"> bla bla', true],
    ];
  }

  /**
   * @group unit
   * @small
   * @covers ::isAMindstormsProject
   * @dataProvider dataProviderIsAMindstormsProject
   */
  public function testIsAMindstormsProject(string $code_xml, bool $expected): void
  {
    $this->assertEquals(
          $expected,
          $this->invokeMethod($this->object, 'isAMindstormsProject', [$code_xml])
      );
  }

  public function dataProviderIsAMindstormsProject(): array
  {
    return [
      'invalid' => ['bla bla bla bla', false],
      'valid 01' => ['"legonxt bla bla', true],
      'valid 02' => ['blaaaa "legoev3/', true],
    ];
  }

  /**
   * @group unit
   * @small
   * @covers ::isAPhiroProject
   * @dataProvider dataProviderIsAPhiroProject
   */
  public function testIsAPhiroProject(string $code_xml, bool $expected): void
  {
    $this->assertEquals(
          $expected,
          $this->invokeMethod($this->object, 'isAPhiroProject', [$code_xml])
      );
  }

  public function dataProviderIsAPhiroProject(): array
  {
    return [
      'invalid' => ['bla bla Phiro im titel bla bla', false],
      'valid' => ['bla bla <brick type="Phiro bla bla', true],
    ];
  }

  protected function setUpCatrobatTestFiles(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_extensions/', RefreshTestEnvHook::$CACHE_DIR.'program_with_extensions/');
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', RefreshTestEnvHook::$CACHE_DIR.'base/');
    $this->extracted_catrobat_file_without_extensions = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '', '');
    $this->extracted_catrobat_file_with_extensions = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'program_with_extensions/', '', '');
  }
}
