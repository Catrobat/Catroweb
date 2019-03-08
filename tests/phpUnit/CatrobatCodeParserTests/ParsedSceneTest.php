<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;

class ParsedSceneTest extends \PHPUnit\Framework\TestCase
{
  protected $scene;

  public function setUp()
  {
    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/SceneProgram/code.xml');
    $this->scene = new ParsedScene($xml_properties->xpath('//scene')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    $this->assertTrue(method_exists($this->scene, $method_name));
  }

  public function provideMethodNames()
  {
    return [
      ['getName'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getNameMustReturnCertainString()
  {
    $expected = 'Scene 1';
    $actual = $this->scene->getName();

    $this->assertEquals($expected, $actual);
  }
}