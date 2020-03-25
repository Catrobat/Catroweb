<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\CodeStatistic;
use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;
use App\Catrobat\Services\CatrobatCodeParser\ParsedSceneProgram;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ParsedSceneProgramTest extends TestCase
{
  protected ParsedSceneProgram $program;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/SceneProgram/code.xml');
    $this->program = new ParsedSceneProgram($xml_properties);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    $this->assertTrue(method_exists($this->program, $method_name));
  }

  /**
   * @return string[][]
   */
  public function provideMethodNames(): array
  {
    return [
      ['hasScenes'],
      ['getCodeStatistic'],
      ['getScenes'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function hasScenesMustReturnTrue(): void
  {
    $this->assertTrue($this->program->hasScenes());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getCodeStatisticMustReturnCodeStatistic(): void
  {
    $actual = $this->program->getCodeStatistic();
    $expected = CodeStatistic::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getScenesMustReturnArrayOfScenes(): void
  {
    $expected = ParsedScene::class;

    foreach ($this->program->getScenes() as $actual)
    {
      $this->assertInstanceOf($expected, $actual);
    }
  }
}
