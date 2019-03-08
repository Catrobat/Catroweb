<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\ScriptFactory;

class ScriptsTest extends \PHPUnit\Framework\TestCase
{
  const TYPE = 'type';
  const CAPTION = 'caption';
  const IMG_FILE = 'img_file';

  protected $script_xml_properties_list;

  public function setUp()
  {
    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->script_xml_properties_list = $xml_properties->xpath('//script');
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    foreach ($this->script_xml_properties_list as $script_xml_properties)
    {
      $script = ScriptFactory::generate($script_xml_properties);
      $this->assertTrue(method_exists($script, $method_name));
    }
  }

  public function provideMethodNames()
  {
    return [
      ['getType'],
      ['getCaption'],
      ['getImgFile'],
      ['getBricks'],
    ];
  }

  /**
   * @test
   * @depends      mustHaveMethod
   * @dataProvider provideScriptXMLProperties
   */
  public function factoryMustGenerateValidScript($script_xml_properties, $expected)
  {
    $actual = ScriptFactory::generate($script_xml_properties);

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    $this->assertTrue(true);
  }

  public function provideScriptXMLProperties()
  {
    $data = [];

    $reference_output =
      file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/script_reference.output', FILE_IGNORE_NEW_LINES);
    $reference_output_index = 0;

    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    foreach ($xml_properties->xpath('//script') as $script_xml_properties)
    {

      $expected = [
        self::TYPE     => $reference_output[$reference_output_index++],
        self::CAPTION  => $reference_output[$reference_output_index++],
        self::IMG_FILE => $reference_output[$reference_output_index++],
      ];
      // To omit '---' after each script information block in file 'script_reference.ouput'
      $reference_output_index++;

      $data[] = [
        $script_xml_properties,
        $expected,
      ];
    }

    return $data;
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function factoryMustGenerateUnknownScriptOtherwise()
  {
    $script_xml_properties = $this->script_xml_properties_list[0];
    $script_xml_properties[Constants::TYPE_ATTRIBUTE] = 'Foo'; // Fake random script
    $actual = ScriptFactory::generate($script_xml_properties);

    $expected = [
      self::TYPE     => Constants::UNKNOWN_SCRIPT,
      self::CAPTION  => 'Unknown Script',
      self::IMG_FILE => Constants::UNKNOWN_SCRIPT_IMG,
    ];

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    return $actual;
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function outCommentedScriptsMustContainOnlyGrayBricks()
  {
    $script_xml_properties = $this->script_xml_properties_list[0];
    $script_xml_properties->commentedOut = 'true'; // Fake out-commented script
    $actual = ScriptFactory::generate($script_xml_properties);

    $expected_img_file = Constants::UNKNOWN_BRICK_IMG;
    foreach ($actual->getBricks() as $brick)
      $this->assertEquals($expected_img_file, $brick->getImgFile());
  }
}