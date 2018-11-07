<?php

namespace Tests\CatrobatCodeParserTests;

use Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedScene;
use function PHPSTORM_META\type;

class ParsedObjectsContainerTest extends \PHPUnit\Framework\TestCase
{
  protected $container;

  public function setUp()
  {
    $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->container = new ParsedScene($xml_properties->xpath('//scene')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   */
  public function mustHaveMethod($method_name)
  {
    $this->assertTrue(method_exists($this->container, $method_name));
  }

  public function provideMethodNames()
  {
    return [
      ['getObjects'],
      ['getBackground'],
    ];
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getBackgroundMustReturnParsedObject()
  {
    $actual = $this->container->getBackground();
    $expected = 'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject';

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getObjectsMustReturnArrayOfParsedObjectOrParsedObjectGroup()
  {
    $expected = [
      'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject',
      'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObjectGroup',
    ];

    foreach ($this->container->getObjects() as $actual)
      $this->assertThat($actual, $this->logicalOr(
        $this->isInstanceOf($expected[0]),
        $this->isInstanceOf($expected[1])
      ));
  }

  /**
   * @test
   */
  public function mustThrowExceptionIfCorruptedGroup()
  {
    $this->expectExceptionMessage('\Exception');

    $xml_properties = simplexml_load_file(__DIR__
      . '/Resources/FaultyPrograms/CorruptedGroupFaultyProgram/code.xml');

    if (!array_key_exists(0, $xml_properties->xpath('//scene')))
    {
      new ParsedScene($xml_properties->xpath('//scene')[0]);
    }
    else
    {
      throw new \Exception("\Exception");
    }

  }
}