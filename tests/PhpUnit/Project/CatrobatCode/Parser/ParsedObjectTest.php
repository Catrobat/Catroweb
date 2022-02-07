<?php

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\ParsedObject;
use App\Project\CatrobatCode\Parser\ParsedObjectAsset;
use App\Project\CatrobatCode\Parser\Scripts\Script;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Project\CatrobatCode\Parser\ParsedObject
 */
class ParsedObjectTest extends TestCase
{
  protected ParsedObject $object;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(RefreshTestEnvHook::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_object = $xml_properties->xpath('//object');
    Assert::assertNotFalse($xml_object);
    $this->object = new ParsedObject($xml_object[0]);
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

    foreach ($this->object->getLooks() as $actual) {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getSoundsMustReturnEmptyArrayOfParsedObjectAsset(): void
  {
    $this->assertTrue([] === $this->object->getSounds());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getScriptsMustReturnArrayOfScript(): void
  {
    $expected = Script::class;

    foreach ($this->object->getScripts() as $actual) {
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
