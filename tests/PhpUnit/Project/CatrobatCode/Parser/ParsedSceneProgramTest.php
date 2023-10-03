<?php

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CodeStatistic;
use App\Project\CatrobatCode\Parser\ParsedScene;
use App\Project\CatrobatCode\Parser\ParsedSceneProgram;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\ParsedSceneProgram
 */
class ParsedSceneProgramTest extends TestCase
{
  protected ParsedSceneProgram $program;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SceneProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $this->program = new ParsedSceneProgram($xml_properties);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->program, $method_name));
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['hasScenes'],
      ['getCodeStatistic'],
      ['getScenes'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testHasScenesMustReturnTrue(): void
  {
    $this->assertTrue($this->program->hasScenes());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetCodeStatisticMustReturnCodeStatistic(): void
  {
    $actual = $this->program->getCodeStatistic();
    $expected = CodeStatistic::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetScenesMustReturnArrayOfScenes(): void
  {
    $expected = ParsedScene::class;

    foreach ($this->program->getScenes() as $actual) {
      $this->assertInstanceOf($expected, $actual);
    }
  }
}
