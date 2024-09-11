<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\ParsedObject;
use App\Project\CatrobatCode\Parser\ParsedObjectGroup;
use App\Project\CatrobatCode\Parser\ParsedObjectsContainer;
use App\Project\CatrobatCode\Parser\ParsedScene;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ParsedObjectsContainer::class)]
class ParsedObjectsContainerTest extends TestCase
{
  protected ParsedScene $container;

  #[\Override]
  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllBricksProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_scene = $xml_properties->xpath('//scene');
    Assert::assertNotFalse($xml_scene);
    $this->container = new ParsedScene($xml_scene[0]);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->container, $method_name));
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['getObjects'],
      ['getBackground'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetBackgroundMustReturnParsedObject(): void
  {
    $actual = $this->container->getBackground();
    $expected = ParsedObject::class;

    $this->assertInstanceOf($expected, $actual);
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetObjectsMustReturnArrayOfParsedObjectOrParsedObjectGroup(): void
  {
    $expected = [
      ParsedObject::class,
      ParsedObjectGroup::class,
    ];

    foreach ($this->container->getObjects() as $actual) {
      $this->assertThat($actual, $this->logicalOr(
        $this->isInstanceOf($expected[0]),
        $this->isInstanceOf($expected[1])
      ));
    }
  }

  /**
   * @throws \Exception
   */
  public function testMustThrowExceptionIfCorruptedGroup(): void
  {
    $this->expectExceptionMessage(\Exception::class);

    $xml_properties = simplexml_load_file(
      BootstrapExtension::$FIXTURES_DIR.'FaultyPrograms/CorruptedGroupFaultyProgram/code.xml'
    );
    Assert::assertNotFalse($xml_properties);

    $xml_scene = $xml_properties->xpath('//scene');
    Assert::assertNotFalse($xml_scene);

    if (!array_key_exists(0, $xml_scene)) {
      new ParsedScene($xml_scene[0]);
    } else {
      throw new \Exception(\Exception::class);
    }
  }
}
