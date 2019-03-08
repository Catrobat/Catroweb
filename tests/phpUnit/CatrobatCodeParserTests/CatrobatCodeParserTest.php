<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\CatrobatCodeParser;
use App\Catrobat\Services\ExtractedCatrobatFile;

class CatrobatCodeParserTest extends \PHPUnit\Framework\TestCase
{
  protected $parser;

  protected function setUp()
  {
    $this->parser = new CatrobatCodeParser();
  }

  /**
   * @test
   * @dataProvider validProgramProvider
   */
  public function mustReturnParsedProgram($extracted_catrobat_program)
  {
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = [
      'App\Catrobat\Services\CatrobatCodeParser\ParsedSimpleProgram',
      'App\Catrobat\Services\CatrobatCodeParser\ParsedSceneProgram',
    ];

    $this->assertThat($actual, $this->logicalOr(
      $this->isInstanceOf($expected[0]),
      $this->isInstanceOf($expected[1])
    ));
  }

  /**
   * @test
   */
  public function mustReturnParsedSimpleProgramIfNoScenes()
  {
    $extracted_catrobat_program = new ExtractedCatrobatFile(__DIR__
      . '/Resources/ValidPrograms/SimpleProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = 'App\Catrobat\Services\CatrobatCodeParser\ParsedSimpleProgram';

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   */
  public function mustReturnParsedSceneProgramIfScenes()
  {
    $extracted_catrobat_program = new ExtractedCatrobatFile(__DIR__
      . '/Resources/ValidPrograms/SceneProgram/', '', '');
    $actual = $this->parser->parse($extracted_catrobat_program);
    $expected = 'App\Catrobat\Services\CatrobatCodeParser\ParsedSceneProgram';

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @dataProvider faultyProgramProvider
   */
  public function mustReturnNullOnError($faulty_program)
  {
    $this->assertNull($this->parser->parse($faulty_program));
  }

  public function validProgramProvider()
  {
    $programs = [];
    $programs[] = [new ExtractedCatrobatFile(__DIR__ . '/Resources/ValidPrograms/SimpleProgram/', '', '')];
    $programs[] = [new ExtractedCatrobatFile(__DIR__ . '/Resources/ValidPrograms/SceneProgram/', '', '')];
    $programs[] = [new ExtractedCatrobatFile(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/', '', '')];

    return $programs;
  }

  public function faultyProgramProvider()
  {
    $programs = [];
    $programs[] = [new ExtractedCatrobatFile(
      __DIR__ . '/Resources/FaultyPrograms/CorruptedGroupFaultyProgram/', '', ''),
    ];
    $programs[] = [new ExtractedCatrobatFile(
      __DIR__ . '/Resources/FaultyPrograms/ScenesWithoutNamesFaultyProgram/', '', ''),
    ];

    return $programs;
  }
}