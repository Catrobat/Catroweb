<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\Services\CatrobatCodeParser\ParsedObject;
use App\Catrobat\Services\CatrobatCodeParser\ParsedObjectGroup;
use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ParsedObjectsContainerTest extends TestCase
{
  protected ParsedScene $container;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    $this->container = new ParsedScene($xml_properties->xpath('//scene')[0]);
  }

  /**
   * @test
   * @dataProvider provideMethodNames
   *
   * @param mixed $method_name
   */
  public function mustHaveMethod($method_name): void
  {
    $this->assertTrue(method_exists($this->container, $method_name));
  }

  /**
   * @return string[][]
   */
  public function provideMethodNames(): array
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
  public function getBackgroundMustReturnParsedObject(): void
  {
    $actual = $this->container->getBackground();
    $expected = ParsedObject::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @test
   * @depends mustHaveMethod
   */
  public function getObjectsMustReturnArrayOfParsedObjectOrParsedObjectGroup(): void
  {
    $expected = [
      ParsedObject::class,
      ParsedObjectGroup::class,
    ];

    foreach ($this->container->getObjects() as $actual)
    {
      $this->assertThat($actual, $this->logicalOr(
        $this->isInstanceOf($expected[0]),
        $this->isInstanceOf($expected[1])
      ));
    }
  }

  /**
   * @test
   */
  public function mustThrowExceptionIfCorruptedGroup()
  {
    $this->expectExceptionMessage(Exception::class);

    $xml_properties = simplexml_load_file(__DIR__
      .'/Resources/FaultyPrograms/CorruptedGroupFaultyProgram/code.xml');

    if (!array_key_exists(0, $xml_properties->xpath('//scene')))
    {
      new ParsedScene($xml_properties->xpath('//scene')[0]);
    }
    else
    {
      throw new Exception(Exception::class);
    }
  }
}
