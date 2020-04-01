<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\CodeStatistic;
use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * @internal
 * @coversNothing
 */
class CodeStatisticTest extends TestCase
{
  protected SimpleXMLElement $xml_properties;

  protected function setUp(): void
  {
    $xml = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    self::assertNotFalse($xml);
    $this->xml_properties = $xml;
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    $code_statistic = new CodeStatistic();
    $this->assertTrue(method_exists($code_statistic, $method_name));
  }

  /**
   * @return string[][]
   */
  public function provideMethodNames(): array
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
   * @test
   * @depends mustHaveMethod
   */
  public function mustComputeCorrectScriptStatistic(): CodeStatistic
  {
    $code_statistic = new CodeStatistic();
    $code_statistic->update(new ParsedScene($this->xml_properties->xpath('//scene')[0]));

    $expected = 34;
    $actual = $code_statistic->getScriptStatistic();

    $this->assertEquals($expected, $actual);

    return $code_statistic;
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   *
   * @param mixed $code_statistic
   */
  public function mustComputeCorrectBrickStatistic($code_statistic): void
  {
    $expected = 170;
    $actual = $code_statistic->getBrickStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   *
   * @param mixed $code_statistic
   */
  public function mustComputeCorrectObjectStatistic($code_statistic): void
  {
    $expected = 16;
    $actual = $code_statistic->getObjectStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   *
   * @param mixed $code_statistic
   */
  public function mustComputeCorrectLookStatistic($code_statistic): void
  {
    $expected = 19;
    $actual = $code_statistic->getLookStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   *
   * @param mixed $code_statistic
   */
  public function mustComputeCorrectSoundStatistic($code_statistic): void
  {
    $expected = 4;
    $actual = $code_statistic->getSoundStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function mustComputeCorrectGlobalVariableStatistic(): CodeStatistic
  {
    $code_statistic = new CodeStatistic();
    $code_statistic->computeVariableStatistic($this->xml_properties);

    $expected = 2;
    $actual = $code_statistic->getGlobalVarStatistic();

    $this->assertEquals($expected, $actual);

    return $code_statistic;
  }

  /**
   * @test
   * @depends mustComputeCorrectGlobalVariableStatistic
   *
   * @param mixed $code_statistic
   */
  public function mustComputeCorrectLocalVariableStatistic($code_statistic): void
  {
    $expected = 0;
    $actual = $code_statistic->getLocalVarStatistic();

    $this->assertEquals($expected, $actual);
  }
}
