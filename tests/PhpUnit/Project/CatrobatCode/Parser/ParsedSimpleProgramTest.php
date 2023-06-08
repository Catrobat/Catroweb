<?php

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CodeStatistic;
use App\Project\CatrobatCode\Parser\ParsedSimpleProgram;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\ParsedSimpleProgram
 */
class ParsedSimpleProgramTest extends TestCase
{
  protected ParsedSimpleProgram $program;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/SimpleProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $this->program = new ParsedSimpleProgram($xml_properties);
  }

  /**
   * @test
   *
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod(mixed $method_name): void
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
    ];
  }

  /**
   * @test
   *
   * @depends mustHaveMethod
   */
  public function hasScenesMustReturnFalse(): void
  {
    $this->assertFalse($this->program->hasScenes());
  }

  /**
   * @test
   *
   * @depends mustHaveMethod
   */
  public function getCodeStatisticMustReturnCodeStatistic(): void
  {
    $actual = $this->program->getCodeStatistic();
    $expected = CodeStatistic::class;

    $this->assertInstanceOf($expected, $actual);
  }
}
