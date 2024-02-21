<?php

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatCode\Parser\ParsedSceneProject;
use App\Project\CatrobatCode\Parser\ParsedSimpleProject;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\CatrobatCodeParser
 */
class CatrobatCodeParserTest extends TestCase
{
  protected CatrobatCodeParser $parser;

  protected function setUp(): void
  {
    $this->parser = new CatrobatCodeParser();
  }

  public function tearDown(): void
  {
    FileHelper::emptyDirectory(BootstrapExtension::$CACHE_DIR);
  }

  #[DataProvider('provideValidProjectData')]
  public function testMustReturnParsedProject(mixed $extracted_catrobat_project): void
  {
    $actual = $this->parser->parse($extracted_catrobat_project);
    $expected = [
      ParsedSimpleProject::class,
      ParsedSceneProject::class,
    ];

    $this->assertThat($actual, $this->logicalOr(
      $this->isInstanceOf($expected[0]),
      $this->isInstanceOf($expected[1])
    ));
  }

  public function testMustReturnParsedSimpleProjectIfNoScenes(): void
  {
    $extracted_catrobat_project = new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SimpleProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_project);
    $expected = ParsedSimpleProject::class;

    $this->assertInstanceOf($expected, $actual);
  }

  public function testMustReturnParsedSceneProjectIfScenes(): void
  {
    $extracted_catrobat_project = new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SceneProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_project);
    $expected = ParsedSceneProject::class;

    $this->assertInstanceOf($expected, $actual);
  }

  #[DataProvider('provideFaultyProjectData')]
  public function testMustReturnNullOnError(ExtractedCatrobatFile $faulty_project): void
  {
    $this->assertNull($this->parser->parse($faulty_project));
  }

  /**
   * @return \App\Project\CatrobatFile\ExtractedCatrobatFile[][]
   */
  public static function provideValidProjectData(): array
  {
    $projects = [];
    $projects[] = [new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SimpleProgram/', '', '')];
    $projects[] = [new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SceneProgram/', '', '')];
    $projects[] = [new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/', '', '')];

    return $projects;
  }

  public static function provideFaultyProjectData(): \Generator
  {
    yield [
      new ExtractedCatrobatFile(
        BootstrapExtension::$FIXTURES_DIR.'FaultyPrograms/CorruptedGroupFaultyProgram/', '', ''),
    ];
    yield [
      new ExtractedCatrobatFile(
        BootstrapExtension::$FIXTURES_DIR.'FaultyPrograms/ScenesWithoutNamesFaultyProgram/', '', ''),
    ];
  }
}
