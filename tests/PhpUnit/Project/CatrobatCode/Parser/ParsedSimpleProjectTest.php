<?php

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CodeStatistic;
use App\Project\CatrobatCode\Parser\ParsedSimpleProject;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\ParsedSimpleProject
 */
class ParsedSimpleProjectTest extends TestCase
{
  protected ParsedSimpleProject $project;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SimpleProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $this->project = new ParsedSimpleProject($xml_properties);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->project, $method_name));
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['hasScenes'],
      ['getCodeStatistic'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testHasScenesMustReturnFalse(): void
  {
    $this->assertFalse($this->project->hasScenes());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetCodeStatisticMustReturnCodeStatistic(): void
  {
    $actual = $this->project->getCodeStatistic();
    $expected = CodeStatistic::class;

    $this->assertInstanceOf($expected, $actual);
  }
}
