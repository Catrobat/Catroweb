<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\Scripts\Script;
use App\Catrobat\CatrobatCode\Parser\Scripts\ScriptFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * @internal
 * @coversNothing
 */
class ScriptsTest extends TestCase
{
  /**
   * @var string
   */
  const TYPE = 'type';
  /**
   * @var string
   */
  const CAPTION = 'caption';
  /**
   * @var string
   */
  const IMG_FILE = 'img_file';

  /**
   * @var SimpleXMLElement[]
   */
  protected array $script_xml_properties_list;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_script = $xml_properties->xpath('//script');
    Assert::assertNotFalse($xml_script);
    $this->script_xml_properties_list = $xml_script;
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    foreach ($this->script_xml_properties_list as $script_xml_properties)
    {
      $script = ScriptFactory::generate($script_xml_properties);
      $this->assertTrue(method_exists($script, $method_name));
    }
  }

  public function provideMethodNames(): array
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
   *
   * @param mixed $script_xml_properties
   * @param mixed $expected
   */
  public function factoryMustGenerateValidScript($script_xml_properties, $expected): void
  {
    $actual = ScriptFactory::generate($script_xml_properties);

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    $this->assertTrue(true);
  }

  /**
   * @return mixed[][]
   */
  public function provideScriptXMLProperties(): array
  {
    $data = [];

    $reference_output =
      file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/script_reference.output', FILE_IGNORE_NEW_LINES);
    Assert::assertNotFalse($reference_output);

    $reference_output_index = 0;

    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);

    $xml_script = $xml_properties->xpath('//script');
    Assert::assertNotFalse($xml_script);

    foreach ($xml_script as $script_xml_properties)
    {
      $expected = [
        self::TYPE => $reference_output[$reference_output_index++],
        self::CAPTION => $reference_output[$reference_output_index++],
        self::IMG_FILE => $reference_output[$reference_output_index++],
      ];
      // To omit '---' after each script information block in file 'script_reference.ouput'
      ++$reference_output_index;

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
  public function factoryMustGenerateUnknownScriptOtherwise(): ?Script
  {
    $script_xml_properties = $this->script_xml_properties_list[0];
    $script_xml_properties[Constants::TYPE_ATTRIBUTE] = 'Foo'; // Fake random script
    $actual = ScriptFactory::generate($script_xml_properties);

    $expected = [
      self::TYPE => Constants::UNKNOWN_SCRIPT,
      self::CAPTION => 'Unknown Script',
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
  public function outCommentedScriptsMustContainOnlyGrayBricks(): void
  {
    $script_xml_properties = $this->script_xml_properties_list[0];
    $script_xml_properties->commentedOut = 'true'; // Fake out-commented script
    $actual = ScriptFactory::generate($script_xml_properties);

    $expected_img_file = Constants::UNKNOWN_BRICK_IMG;
    foreach ($actual->getBricks() as $brick)
    {
      $this->assertEquals($expected_img_file, $brick->getImgFile());
    }
  }
}
