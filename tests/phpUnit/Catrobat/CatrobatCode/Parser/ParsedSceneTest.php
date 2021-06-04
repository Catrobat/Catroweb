<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\ParsedScene;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\CatrobatCode\Parser\ParsedScene
 */
class ParsedSceneTest extends TestCase
{
  protected ParsedScene $scene;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/SceneProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_scene = $xml_properties->xpath('//scene');
    Assert::assertNotFalse($xml_scene);
    $this->scene = new ParsedScene($xml_scene[0]);
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
