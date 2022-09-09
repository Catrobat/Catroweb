<?php

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatCode\Parser\ParsedSceneProgram;
use App\Project\CatrobatCode\Parser\ParsedSimpleProgram;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
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

  /**
   * @test
   *
   * @dataProvider validProgramProvider
   *
   * @param mixed $extracted_catrobat_program
   */
  public function mustReturnParsedProgram($extracted_catrobat_program): void
  {
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = [
      ParsedSimpleProgram::class,
      ParsedSceneProgram::class,
    ];

    $this->assertThat($actual, $this->logicalOr(
      $this->isInstanceOf($expected[0]),
      $this->isInstanceOf($expected[1])
    ));
  }

  /**
   * @test
   */
  public function mustReturnParsedSimpleProgramIfNoScenes(): void
  {
    $extracted_catrobat_program = new ExtractedCatrobatFile(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/SimpleProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = ParsedSimpleProgram::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   */
  public function mustReturnParsedSceneProgramIfScenes(): void
  {
    $extracted_catrobat_program = new ExtractedCatrobatFile(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/SceneProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = ParsedSceneProgram::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   *
   * @dataProvider faultyProgramProvider
   *
   * @param mixed $faulty_program
   */
  public function mustReturnNullOnError($faulty_program): void
  {
    $this->assertNull($this->parser->parse($faulty_program));
  }

  /**
   * @return \App\Project\CatrobatFile\ExtractedCatrobatFile[][]
   */
  public function validProgramProvider(): array
  {
    $programs = [];
    $programs[] = [new ExtractedCatrobatFile(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/SimpleProgram/', '', '')];
    $programs[] = [new ExtractedCatrobatFile(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/SceneProgram/', '', '')];
    $programs[] = [new ExtractedCatrobatFile(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/', '', '')];

    return $programs;
  }

  /**
   * @return \App\Project\CatrobatFile\ExtractedCatrobatFile[][]
   */
  public function faultyProgramProvider(): array
  {
    $programs = [];
    $programs[] = [new ExtractedCatrobatFile(
      RefreshTestEnvHook::$FIXTURES_DIR.'FaultyPrograms/CorruptedGroupFaultyProgram/', '', ''),
    ];
    $programs[] = [new ExtractedCatrobatFile(
      RefreshTestEnvHook::$FIXTURES_DIR.'FaultyPrograms/ScenesWithoutNamesFaultyProgram/', '', ''),
    ];

    return $programs;
  }
}
