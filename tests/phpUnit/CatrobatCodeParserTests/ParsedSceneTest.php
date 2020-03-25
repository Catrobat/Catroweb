<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ParsedSceneTest extends TestCase
{
  protected ParsedScene $scene;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/SceneProgram/code.xml');
    $this->scene = new ParsedScene($xml_properties->xpath('//scene')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    $this->assertTrue(method_exists($this->scene, $method_name));
  }

  /**
   * @return string[][]
   */
  public function provideMethodNames(): array
  {
    return [
      ['getName'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getNameMustReturnCertainString(): void
  {
    $expected = 'Scene 1';
    $actual = $this->scene->getName();

    $this->assertEquals($expected, $actual);
  }
}
