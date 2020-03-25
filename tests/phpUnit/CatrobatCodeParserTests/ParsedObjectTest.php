<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedObject;
use App\Catrobat\Services\CatrobatCodeParser\ParsedObjectAsset;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ParsedObjectTest extends TestCase
{
  protected ParsedObject $object;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->object = new ParsedObject($xml_properties->xpath('//object')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    $this->assertTrue(method_exists($this->object, $method_name));
  }

  public function provideMethodNames(): array
  {
    return [
      ['getName'],
      ['getScripts'],
      ['getSounds'],
      ['getLooks'],
      ['isGroup'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function isGroupMustReturnFalse(): void
  {
    $this->assertFalse($this->object->isGroup());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getLooksMustReturnArrayOfParsedObjectAsset(): void
  {
    $expected = ParsedObjectAsset::class;

    foreach ($this->object->getLooks() as $actual)
    {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getSoundsMustReturnArrayOfParsedObjectAsset(): void
  {
    $expected = ParsedObjectAsset::class;

    $this->assertTrue($this->object->getSounds() === []);

    foreach ($this->object->getSounds() as $actual)
    {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getScriptsMustReturnArrayOfScript(): void
  {
    $expected = Script::class;

    foreach ($this->object->getScripts() as $actual)
    {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getNameMustReturnCertainString(): void
  {
    $expected = 'Background';
    $actual = $this->object->getName();

    $this->assertEquals($expected, $actual);
  }
}
