<?php

namespace Project\Extension;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\Extension\ProjectExtensionManager;
use App\System\Testing\PhpUnit\DefaultTestCase;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @coversDefaultClass \App\Project\Extension\ProjectExtensionManager
 */
class ProjectExtensionManagerTest extends DefaultTestCase
{
  protected ExtractedCatrobatFile $extracted_catrobat_file_with_extensions;
  protected ExtractedCatrobatFile $extracted_catrobat_file_without_extensions;

  protected MockObject|ProjectExtensionManager $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(ProjectExtensionManager::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->setUpCatrobatTestFiles();
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers ::addExtensions
   */
  public function testAddExtensions(): void
  {
    $project = $this
      ->getMockBuilder(Project::class)
      ->onlyMethods(['removeAllExtensions'])
      ->getMockForAbstractClass()
    ;

    $this->object = $this->getMockBuilder(ProjectExtensionManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'addArduinoExtensions',
        'addPhiroExtensions',
        'addEmbroideryExtensions',
        'addMindstormsExtensions',
        'addMultiplayerExtensions',
        'getExtension',
        'saveProject',
      ])
      ->getMockForAbstractClass()
    ;

    $project->expects($this->once())->method('removeAllExtensions');
    $this->object->expects($this->once())->method('addArduinoExtensions');
    $this->object->expects($this->once())->method('addPhiroExtensions');
    $this->object->expects($this->once())->method('addEmbroideryExtensions');
    $this->object->expects($this->once())->method('addMindstormsExtensions');
    $this->object->expects($this->once())->method('addMultiplayerExtensions');
    $this->object->expects($this->once())->method('saveProject');

    $this->object->addExtensions($this->extracted_catrobat_file_with_extensions, $project);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers ::isAnEmbroideryProject
   */
  #[DataProvider('provideIsAnEmbroideryProjectData')]
  public function testIsAnEmbroideryProject(string $code_xml, bool $expected): void
  {
    $this->assertEquals(
      $expected,
      $this->invokeMethod($this->object, 'isAnEmbroideryProject', [$code_xml])
    );
  }

  public static function provideIsAnEmbroideryProjectData(): array
  {
    return [
      'invalid' => ['bla bla <brick type="NoStitchBrick" bla bla', false],
      'valid' => ['bla bla <brick type="StitchBrick"> bla bla', true],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers ::isAMindstormsProject
   */
  #[DataProvider('provideIsAMindstormsProjectData')]
  public function testIsAMindstormsProject(string $code_xml, bool $expected): void
  {
    $this->assertEquals(
      $expected,
      $this->invokeMethod($this->object, 'isAMindstormsProject', [$code_xml])
    );
  }

  public static function provideIsAMindstormsProjectData(): array
  {
    return [
      'invalid' => ['bla bla bla bla', false],
      'valid 01' => ['"legonxt bla bla', true],
      'valid 02' => ['blaaaa "legoev3/', true],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers ::isAPhiroProject
   */
  #[DataProvider('provideIsAPhiroProjectData')]
  public function testIsAPhiroProject(string $code_xml, bool $expected): void
  {
    $this->assertEquals(
      $expected,
      $this->invokeMethod($this->object, 'isAPhiroProject', [$code_xml])
    );
  }

  public static function provideIsAPhiroProjectData(): array
  {
    return [
      'invalid' => ['bla bla Phiro im titel bla bla', false],
      'valid' => ['bla bla <brick type="Phiro bla bla', true],
    ];
  }

  protected function setUpCatrobatTestFiles(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_extensions/', BootstrapExtension::$CACHE_DIR.'program_with_extensions/');
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', BootstrapExtension::$CACHE_DIR.'base/');
    $this->extracted_catrobat_file_without_extensions = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '', '');
    $this->extracted_catrobat_file_with_extensions = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'program_with_extensions/', '', '');
  }
}
