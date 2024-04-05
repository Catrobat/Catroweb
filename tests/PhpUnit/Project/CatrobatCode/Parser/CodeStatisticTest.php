<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CodeStatistic;
use App\Project\CatrobatCode\Parser\ParsedScene;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\CodeStatistic
 */
class CodeStatisticTest extends TestCase
{
  protected \SimpleXMLElement $xml_properties;

  protected function setUp(): void
  {
    $xml = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $this->xml_properties = $xml;
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $code_statistic = new CodeStatistic();
    $this->assertTrue(method_exists($code_statistic, $method_name));
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['update'],
      ['getScriptStatistic'],
      ['getBrickStatistic'],
      ['getBrickTypeStatistic'],
      ['getObjectStatistic'],
      ['getLookStatistic'],
      ['getSoundStatistic'],
      ['getGlobalVarStatistic'],
      ['getLocalVarStatistic'],
      ['computeVariableStatistic'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testMustComputeCorrectScriptStatistic(): CodeStatistic
  {
    $code_statistic = new CodeStatistic();
    $code_statistic->update(new ParsedScene($this->xml_properties->xpath('//scene')[0]));

    $expected = 34;
    $actual = $code_statistic->getScriptStatistic();

    $this->assertEquals($expected, $actual);

    return $code_statistic;
  }

  /**
   * @depends testMustComputeCorrectScriptStatistic
   */
  public function testMustComputeCorrectBrickStatistic(mixed $code_statistic): void
  {
    $expected = 170;
    $actual = $code_statistic->getBrickStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @depends testMustComputeCorrectScriptStatistic
   */
  public function testMustComputeCorrectObjectStatistic(mixed $code_statistic): void
  {
    $expected = 16;
    $actual = $code_statistic->getObjectStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @depends testMustComputeCorrectScriptStatistic
   */
  public function testMustComputeCorrectLookStatistic(mixed $code_statistic): void
  {
    $expected = 19;
    $actual = $code_statistic->getLookStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @depends testMustComputeCorrectScriptStatistic
   */
  public function testMustComputeCorrectSoundStatistic(mixed $code_statistic): void
  {
    $expected = 4;
    $actual = $code_statistic->getSoundStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testMustComputeCorrectGlobalVariableStatistic(): CodeStatistic
  {
    $code_statistic = new CodeStatistic();
    $code_statistic->computeVariableStatistic($this->xml_properties);

    $expected = 2;
    $actual = $code_statistic->getGlobalVarStatistic();

    $this->assertEquals($expected, $actual);

    return $code_statistic;
  }

  /**
   * @depends testMustComputeCorrectGlobalVariableStatistic
   */
  public function testMustComputeCorrectLocalVariableStatistic(mixed $code_statistic): void
  {
    $expected = 0;
    $actual = $code_statistic->getLocalVarStatistic();

    $this->assertEquals($expected, $actual);
  }
}
