<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\ParsedObject;
use App\Catrobat\CatrobatCode\Parser\ParsedObjectGroup;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\CatrobatCode\Parser\ParsedObjectGroup
 */
class ParsedObjectGroupTest extends TestCase
{
  protected ParsedObjectGroup $group;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_object = $xml_properties->xpath('//object[@type="GroupSprite"]');
    Assert::assertNotFalse($xml_object);
    $this->group = new ParsedObjectGroup($xml_object[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    $this->assertTrue(method_exists($this->group, $method_name));
  }

  public function provideMethodNames(): array
  {
    return [
      ['getName'],
      ['addObject'],
      ['getObjects'],
      ['isGroup'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function isGroupMustReturnTrue(): void
  {
    $this->assertTrue($this->group->isGroup());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getObjectsMustReturnEmptyArrayOfParsedObject(): void
  {
    $this->assertTrue([] === $this->group->getObjects());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function addObjectMustAddObjectToObjects(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_object = $xml_properties->xpath('//object');
    Assert::assertNotFalse($xml_object);
    $this->group->addObject(new ParsedObject($xml_object[0]));
    $this->assertNotEmpty($this->group->getObjects());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getNameMustReturnCertainString(): void
  {
    $expected = 'TestGroup';
    $actual = $this->group->getName();

    $this->assertEquals($expected, $actual);
  }
}
