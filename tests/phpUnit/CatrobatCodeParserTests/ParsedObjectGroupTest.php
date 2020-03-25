<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedObject;
use App\Catrobat\Services\CatrobatCodeParser\ParsedObjectGroup;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ParsedObjectGroupTest extends TestCase
{
  protected ParsedObjectGroup $group;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->group = new ParsedObjectGroup($xml_properties->xpath('//object[@type="GroupSprite"]')[0]);
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
  public function getObjectsMustReturnArrayOfParsedObject(): void
  {
    $expected = ParsedObject::class;

    $this->assertTrue($this->group->getObjects() === []);

    foreach ($this->group->getObjects() as $actual)
    {
      $this->assertInstanceOf($expected, $actual);
    }
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function addObjectMustAddObjectToObjects(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->group->addObject(new ParsedObject($xml_properties->xpath('//object')[0]));
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
