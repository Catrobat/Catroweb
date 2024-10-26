<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\ParsedScene;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ParsedScene::class)]
class ParsedSceneTest extends TestCase
{
  protected ParsedScene $scene;

  #[\Override]
  protected function setUp(): void
  {
    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/SceneProgram/code.xml');
    Assert::assertNotFalse($xml_properties);
    $xml_scene = $xml_properties->xpath('//scene');
    Assert::assertNotFalse($xml_scene);
    $this->scene = new ParsedScene($xml_scene[0]);
  }

  #[DataProvider('provideMethodNames')]
  public function testMustHaveMethod(mixed $method_name): void
  {
    $this->assertTrue(method_exists($this->scene, $method_name));
  }

  /**
   * @return string[][]
   */
  public static function provideMethodNames(): array
  {
    return [
      ['getName'],
    ];
  }

  /**
   * @depends testMustHaveMethod
   */
  public function testGetNameMustReturnCertainString(): void
  {
    $expected = 'Scene 1';
    $actual = $this->scene->getName();

    $this->assertEquals($expected, $actual);
  }
}
