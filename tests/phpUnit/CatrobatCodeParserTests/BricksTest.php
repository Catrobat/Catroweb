<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Bricks\BrickFactory;

class BricksTest extends \PHPUnit\Framework\TestCase
{
  const TYPE = 'type';
  const CAPTION = 'caption';
  const IMG_FILE = 'img_file';

  protected $brick_xml_properties_list;

  public function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->brick_xml_properties_list = $xml_properties->xpath('//brick');
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    foreach ($this->brick_xml_properties_list as $brick_xml_properties)
    {
      $script = BrickFactory::generate($brick_xml_properties);
      $this->assertTrue(method_exists($script, $method_name));
    }
  }

  public function provideMethodNames()
  {
    return [
      ['getType'],
      ['getCaption'],
      ['getImgFile'],
    ];
  }

  /**
   * @test
   * @depends      mustHaveMethod
   * @dataProvider provideBrickXMLProperties
   */
  public function factoryMustGenerateValidBrick($brick_xml_properties, $expected)
  {
    $actual = BrickFactory::generate($brick_xml_properties);

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    $this->assertTrue(true);
  }

  public function provideBrickXMLProperties()
  {
    $data = [];

    $reference_output =
      file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/brick_reference.output', FILE_IGNORE_NEW_LINES);
    $reference_output_index = 0;

    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    foreach ($xml_properties->xpath('//brick') as $brick_xml_properties)
    {

      $expected = [
        self::TYPE     => $reference_output[$reference_output_index++],
        self::CAPTION  => $reference_output[$reference_output_index++],
        self::IMG_FILE => $reference_output[$reference_output_index++],
      ];
      // To omit '---' after each script information block in file 'script_reference.ouput'
      $reference_output_index++;

      $data[] = [
        $brick_xml_properties,
        $expected,
      ];
    }

    return $data;
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function factoryMustGenerateUnknownBrickOtherwise()
  {
    $brick_xml_properties = $this->brick_xml_properties_list[0];
    $brick_xml_properties[Constants::TYPE_ATTRIBUTE] = 'Foo'; // Fake random script
    $actual = BrickFactory::generate($brick_xml_properties);

    $expected = [
      self::TYPE     => Constants::UNKNOWN_BRICK,
      self::CAPTION  => 'Unknown Brick',
      self::IMG_FILE => Constants::UNKNOWN_BRICK_IMG,
    ];

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    return $actual;
  }
}