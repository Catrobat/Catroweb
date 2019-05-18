<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedObjectAsset;

class ParsedObjectAssetTest extends \PHPUnit\Framework\TestCase
{
  protected $assets;

  public function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->assets[] = new ParsedObjectAsset($xml_properties->xpath('//look')[0]);
    $this->assets[] = new ParsedObjectAsset($xml_properties->xpath('//sound')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    $this->assertTrue(method_exists($this->assets[0], $method_name));
    $this->assertTrue(method_exists($this->assets[1], $method_name));
  }

  public function provideMethodNames()
  {
    return [
      ['getFileName'],
      ['getName'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getFileNameMustReturnCertainString()
  {
    $expected = [
      'e3b880f6b5eb89981ddb0cf18c545e4d_Mars (Landscape).png',
      '0377a7476136e5e8c780c64a4828922d_AlienCreak1.wav',
    ];
    $actual = [
      $this->assets[0]->getFileName(),
      $this->assets[1]->getFileName(),
    ];

    $this->assertEquals($expected[0], $actual[0]);
    $this->assertEquals($expected[1], $actual[1]);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getNameMustReturnCertainString()
  {
    $expected = [
      'Mars (Landscape)',
      'AlienCreak1',
    ];

    $actual = [
      $this->assets[0]->getName(),
      $this->assets[1]->getName(),
    ];

    $this->assertEquals($expected[0], $actual[0]);
    $this->assertEquals($expected[1], $actual[1]);
  }
}