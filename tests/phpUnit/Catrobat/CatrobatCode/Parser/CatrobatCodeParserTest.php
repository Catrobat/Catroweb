<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\CatrobatCodeParser;
use App\Catrobat\CatrobatCode\Parser\ParsedSceneProgram;
use App\Catrobat\CatrobatCode\Parser\ParsedSimpleProgram;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\CatrobatCode\Parser\CatrobatCodeParser
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
    $extracted_catrobat_program = new ExtractedCatrobatFile(__DIR__
        .'/Resources/ValidPrograms/SimpleProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = ParsedSimpleProgram::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   */
  public function mustReturnParsedSceneProgramIfScenes(): void
  {
    $extracted_catrobat_program = new ExtractedCatrobatFile(__DIR__
        .'/Resources/ValidPrograms/SceneProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = ParsedSceneProgram::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @dataProvider faultyProgramProvider
   *
   * @param mixed $faulty_program
   */
  public function mustReturnNullOnError($faulty_program): void
  {
    $this->assertNull($this->parser->parse($faulty_program));
  }

  /**
   * @return \App\Catrobat\Services\ExtractedCatrobatFile[][]
   */
  public function validProgramProvider(): array
  {
    $programs = [];
    $programs[] = [new ExtractedCatrobatFile(__DIR__.'/Resources/ValidPrograms/SimpleProgram/', '', '')];
    $programs[] = [new ExtractedCatrobatFile(__DIR__.'/Resources/ValidPrograms/SceneProgram/', '', '')];
    $programs[] = [new ExtractedCatrobatFile(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/', '', '')];

    return $programs;
  }

  /**
   * @return \App\Catrobat\Services\ExtractedCatrobatFile[][]
   */
  public function faultyProgramProvider(): array
  {
    $programs = [];
    $programs[] = [new ExtractedCatrobatFile(
      __DIR__.'/Resources/FaultyPrograms/CorruptedGroupFaultyProgram/', '', ''),
    ];
    $programs[] = [new ExtractedCatrobatFile(
      __DIR__.'/Resources/FaultyPrograms/ScenesWithoutNamesFaultyProgram/', '', ''),
    ];

    return $programs;
  }
}
