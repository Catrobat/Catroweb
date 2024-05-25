<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\Scripts\Script;
use App\Project\CatrobatCode\Parser\Scripts\ScriptFactory;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ScriptsTest extends TestCase
{
  public const string TYPE = 'type';

  public const string CAPTION = 'caption';

  public const string IMG_FILE = 'img_file';

  /**
   * @var \SimpleXMLElement[]
   */
  protected array $script_xml_properties_list;

  #[\Override]
  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_script = $xml_properties->xpath('//script');
    Assert::assertNotFalse($xml_script);
    $this->script_xml_properties_list = $xml_script;
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    foreach ($this->script_xml_properties_list as $script_xml_properties) {
      $script = ScriptFactory::generate($script_xml_properties);
      $this->assertTrue(method_exists($script, $method_name));
    }
  }

  public static function provideMethodNames(): array
  {
    return [
      ['getType'],
      ['getCaption'],
      ['getImgFile'],
      ['getBricks'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  #[DataProvider('provideScriptXMLProperties')]
  public function testFactoryMustGenerateValidScript(mixed $script_xml_properties, mixed $expected): void
  {
    $actual = ScriptFactory::generate($script_xml_properties);

    $this->assertEquals($expected[self::TYPE], $actual->getType());
    $this->assertEquals($expected[self::CAPTION], $actual->getCaption());
    $this->assertEquals($expected[self::IMG_FILE], $actual->getImgFile());
  }

  /**
   * @return mixed[][]
   */
  public static function provideScriptXMLProperties(): array
  {
    $data = [];

    $reference_output =
      file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/script_reference.output', FILE_IGNORE_NEW_LINES);
    Assert::assertNotFalse($reference_output);

    $reference_output_index = 0;

    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);

    $xml_script = $xml_properties->xpath('//script');
    Assert::assertNotFalse($xml_script);

    foreach ($xml_script as $script_xml_properties) {
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
   * @depends testMustHaveMethod
   */
  public function testFactoryMustGenerateUnknownScriptOtherwise(): ?Script
  {
    $script_xml_properties = $this->script_xml_properties_list[0];
    // @phpstan-ignore-next-line
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
   * @depends testMustHaveMethod
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testOutCommentedScriptsMustContainOnlyGrayBricks(): void
  {
    $script_xml_properties = $this->script_xml_properties_list[0];
    $script_xml_properties->commentedOut = 'true'; // Fake out-commented script
    $actual = ScriptFactory::generate($script_xml_properties);

    $expected_img_file = Constants::UNKNOWN_BRICK_IMG;
    foreach ($actual->getBricks() as $brick) {
      $this->assertEquals($expected_img_file, $brick->getImgFile());
    }
  }
}
