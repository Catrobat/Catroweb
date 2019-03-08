<?php

namespace tests\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedObject;

class ParsedObjectTest extends \PHPUnit\Framework\TestCase
{
  /**
   * @var ParsedObject
   */
  protected $object;

  public function setUp()
  {
    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->object = new ParsedObject($xml_properties->xpath('//object')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    $this->assertTrue(method_exists($this->object, $method_name));
  }

  public function provideMethodNames()
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
  public function isGroupMustReturnFalse()
  {
    $this->assertFalse($this->object->isGroup());
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getLooksMustReturnArrayOfParsedObjectAsset()
  {
    $expected = 'App\Catrobat\Services\CatrobatCodeParser\ParsedObjectAsset';

    foreach ($this->object->getLooks() as $actual)
      $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getSoundsMustReturnArrayOfParsedObjectAsset()
  {
//        $expected = 'App\Catrobat\Services\CatrobatCodeParser\ParsedObjectAsset';

    $this->assertTrue($this->object->getSounds() === []);

//        foreach($this->object->getSounds() as $actual)
//            $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getScriptsMustReturnArrayOfScript()
  {
    $expected = 'App\Catrobat\Services\CatrobatCodeParser\Scripts\Script';

    foreach ($this->object->getScripts() as $actual)
      $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getNameMustReturnCertainString()
  {
    $expected = 'Background';
    $actual = $this->object->getName();

    $this->assertEquals($expected, $actual);
  }
}