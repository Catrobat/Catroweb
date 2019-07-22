<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\CodeStatistic;
use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;

class CodeStatisticTest extends \PHPUnit\Framework\TestCase
{
  protected $xml_properties;

  public function setUp(): void
  {
    $this->xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    $code_statistic = new CodeStatistic();
    $this->assertTrue(method_exists($code_statistic, $method_name));
  }

  public function provideMethodNames()
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
  public function mustComputeCorrectScriptStatistic()
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
   */
  public function mustComputeCorrectBrickStatistic($code_statistic)
  {
    $expected = 170;
    $actual = $code_statistic->getBrickStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   */
  public function mustComputeCorrectObjectStatistic($code_statistic)
  {
    $expected = 16;
    $actual = $code_statistic->getObjectStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   */
  public function mustComputeCorrectLookStatistic($code_statistic)
  {
    $expected = 19;
    $actual = $code_statistic->getLookStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustComputeCorrectScriptStatistic
   */
  public function mustComputeCorrectSoundStatistic($code_statistic)
  {
    $expected = 4;
    $actual = $code_statistic->getSoundStatistic();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function mustComputeCorrectGlobalVariableStatistic()
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
   */
  public function mustComputeCorrectLocalVariableStatistic($code_statistic)
  {
    $expected = 0;
    $actual = $code_statistic->getLocalVarStatistic();

    $this->assertEquals($expected, $actual);
  }
}






