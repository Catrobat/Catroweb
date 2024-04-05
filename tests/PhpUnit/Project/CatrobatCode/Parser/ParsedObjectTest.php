<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\ParsedObject;
use App\Project\CatrobatCode\Parser\ParsedObjectAsset;
use App\Project\CatrobatCode\Parser\Scripts\Script;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\ParsedObject
 */
class ParsedObjectTest extends TestCase
{
  protected ParsedObject $object;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_object = $xml_properties->xpath('//object');
    Assert::assertNotFalse($xml_object);
    $this->object = new ParsedObject($xml_object[0]);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->object, $method_name));
  }

  public static function provideMethodNames(): array
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
   * @depends testMustHaveMethod
   */
  public function testIsGroupMustReturnFalse(): void
  {
    $this->assertFalse($this->object->isGroup());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetLooksMustReturnArrayOfParsedObjectAsset(): void
  {
    $expected = ParsedObjectAsset::class;

    foreach ($this->object->getLooks() as $actual) {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetSoundsMustReturnEmptyArrayOfParsedObjectAsset(): void
  {
    $this->assertTrue([] === $this->object->getSounds());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetScriptsMustReturnArrayOfScript(): void
  {
    $expected = Script::class;

    foreach ($this->object->getScripts() as $actual) {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetNameMustReturnCertainString(): void
  {
    $expected = 'Background';
    $actual = $this->object->getName();

    $this->assertEquals($expected, $actual);
  }
}
