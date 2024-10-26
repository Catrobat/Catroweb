<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\ParsedObjectAsset;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ParsedObjectAsset::class)]
class ParsedObjectAssetTest extends TestCase
{
  /**
   * @var ParsedObjectAsset[]
   */
  protected array $assets = [];

  #[\Override]
  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    $this->assets[] = new ParsedObjectAsset($xml_properties->xpath('//look')[0]);
    $this->assets[] = new ParsedObjectAsset($xml_properties->xpath('//sound')[0]);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->assets[0], $method_name));
    $this->assertTrue(method_exists($this->assets[1], $method_name));
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['getFileName'],
      ['getName'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetFileNameMustReturnCertainString(): void
  {
    $expected = [
      'e3b880f6b5eb89981ddb0cf18c545e4d_Mars%20%28Landscape%29.png',
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
   * @depends testMustHaveMethod
   */
  public function testGetNameMustReturnCertainString(): void
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
