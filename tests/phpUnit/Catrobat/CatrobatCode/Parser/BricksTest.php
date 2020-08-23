<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\Bricks\Brick;
use App\Catrobat\CatrobatCode\Parser\Bricks\BrickFactory;
use App\Catrobat\CatrobatCode\Parser\Constants;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * @internal
 * @coversNothing
 */
class BricksTest extends TestCase
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
   * @var SimpleXMLElement[]|bool
   */
  protected $brick_xml_properties_list;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->brick_xml_properties_list = $xml_properties->xpath('//brick');
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    foreach ($this->brick_xml_properties_list as $brick_xml_properties)
    {
      $script = BrickFactory::generate($brick_xml_properties);
      $this->assertTrue(method_exists($script, $method_name));
    }
  }

  /**
   * @return string[][]
   */
  public function provideMethodNames(): array
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
   *
   * @param mixed $brick_xml_properties
   * @param mixed $expected
   */
  public function factoryMustGenerateValidBrick($brick_xml_properties, $expected): void
  {
    $actual = BrickFactory::generate($brick_xml_properties);

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    $this->assertTrue(true);
  }

  /**
   * @return mixed[][]
   */
  public function provideBrickXMLProperties(): array
  {
    $data = [];

    $reference_output =
      file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/brick_reference.output', FILE_IGNORE_NEW_LINES);
    $reference_output_index = 0;

    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    foreach ($xml_properties->xpath('//brick') as $brick_xml_properties)
    {
      $expected = [
        self::TYPE => $reference_output[$reference_output_index++],
        self::CAPTION => $reference_output[$reference_output_index++],
        self::IMG_FILE => $reference_output[$reference_output_index++],
      ];
      // To omit '---' after each script information block in file 'script_reference.ouput'
      ++$reference_output_index;

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
  public function factoryMustGenerateUnknownBrickOtherwise(): ?Brick
  {
    $brick_xml_properties = $this->brick_xml_properties_list[0];
    $brick_xml_properties[Constants::TYPE_ATTRIBUTE] = 'Foo'; // Fake random script
    $actual = BrickFactory::generate($brick_xml_properties);

    $expected = [
      self::TYPE => Constants::UNKNOWN_BRICK,
      self::CAPTION => 'Unknown Brick',
      self::IMG_FILE => Constants::UNKNOWN_BRICK_IMG,
    ];

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());

    return $actual;
  }
}
