<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\Bricks\Brick;
use App\Project\CatrobatCode\Parser\Bricks\BrickFactory;
use App\Project\CatrobatCode\Parser\Constants;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class BricksTest extends TestCase
{
  public const string TYPE = 'type';
  public const string CAPTION = 'caption';
  public const string IMG_FILE = 'img_file';

  /**
   * @var \SimpleXMLElement[]|bool
   */
  protected $brick_xml_properties_list;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    $this->brick_xml_properties_list = $xml_properties->xpath('//brick');
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    foreach ($this->brick_xml_properties_list as $brick_xml_properties) {
      $script = BrickFactory::generate($brick_xml_properties);
      $this->assertTrue(method_exists($script, $method_name));
    }
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['getType'],
      ['getCaption'],
      ['getImgFile'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  #[DataProvider('provideBrickXMLProperties')]
  public function testFactoryMustGenerateValidBrick(mixed $brick_xml_properties, mixed $expected): void
  {
    $actual = BrickFactory::generate($brick_xml_properties);

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());
  }

  /**
   * @return mixed[][]
   */
  public static function provideBrickXMLProperties(): array
  {
    $data = [];

    $reference_output =
      file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/brick_reference.output', FILE_IGNORE_NEW_LINES);
    $reference_output_index = 0;

    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    foreach ($xml_properties->xpath('//brick') as $brick_xml_properties) {
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
   * @depends testMustHaveMethod
   */
  public function testFactoryMustGenerateUnknownBrickOtherwise(): ?Brick
  {
    $brick_xml_properties = $this->brick_xml_properties_list[0];
    // @phpstan-ignore-next-line
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
