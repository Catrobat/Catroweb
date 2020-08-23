<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\ParsedObject;
use App\Catrobat\CatrobatCode\Parser\ParsedObjectGroup;
use App\Catrobat\CatrobatCode\Parser\ParsedScene;
use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\CatrobatCode\Parser\ParsedObjectsContainer
 */
class ParsedObjectsContainerTest extends TestCase
{
  protected ParsedScene $container;

  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_scene = $xml_properties->xpath('//scene');
    Assert::assertNotFalse($xml_scene);
    $this->container = new ParsedScene($xml_scene[0]);
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
   *
   * @throws Exception
   */
  public function mustThrowExceptionIfCorruptedGroup(): void
  {
    $this->expectExceptionMessage(Exception::class);

    $xml_properties = simplexml_load_file(
        __DIR__.'/Resources/FaultyPrograms/CorruptedGroupFaultyProgram/code.xml'
    );
    Assert::assertNotFalse($xml_properties);

    $xml_scene = $xml_properties->xpath('//scene');
    Assert::assertNotFalse($xml_scene);

    if (!array_key_exists(0, $xml_scene))
    {
      new ParsedScene($xml_scene[0]);
    }
    else
    {
      throw new Exception(Exception::class);
    }
  }
}
