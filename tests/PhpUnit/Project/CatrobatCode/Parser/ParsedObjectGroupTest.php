<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\ParsedObject;
use App\Project\CatrobatCode\Parser\ParsedObjectGroup;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Project\CatrobatCode\Parser\ParsedObjectGroup
 */
class ParsedObjectGroupTest extends TestCase
{
  protected ParsedObjectGroup $group;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_object = $xml_properties->xpath('//object[@type="GroupSprite"]');
    Assert::assertNotFalse($xml_object);
    $this->group = new ParsedObjectGroup($xml_object[0]);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->group, $method_name));
  }

  public static function provideMethodNames(): array
  {
    return [
      ['getName'],
      ['addObject'],
      ['getObjects'],
      ['isGroup'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testIsGroupMustReturnTrue(): void
  {
    $this->assertTrue($this->group->isGroup());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetObjectsMustReturnEmptyArrayOfParsedObject(): void
  {
    $this->assertTrue([] === $this->group->getObjects());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testAddObjectMustAddObjectToObjects(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_object = $xml_properties->xpath('//object');
    Assert::assertNotFalse($xml_object);
    $this->group->addObject(new ParsedObject($xml_object[0]));
    $this->assertNotEmpty($this->group->getObjects());
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetNameMustReturnCertainString(): void
  {
    $expected = 'TestGroup';
    $actual = $this->group->getName();

    $this->assertEquals($expected, $actual);
  }
}
