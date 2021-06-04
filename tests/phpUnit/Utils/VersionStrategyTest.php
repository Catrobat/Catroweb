<?php

namespace Tests\phpUnit\Utils;

use App\Utils\VersionStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Utils\VersionStrategy
 */
class VersionStrategyTest extends CatrowebTestCase
{
  /**
   * @var VersionStrategy|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(VersionStrategy::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(VersionStrategy::class));
    $this->assertInstanceOf(VersionStrategy::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(VersionStrategyInterface::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new VersionStrategy('1.2.3');
    $this->assertInstanceOf(VersionStrategy::class, $this->object);
  }

  /**
   * @group unit
   * @small
   * @covers       \App\Utils\VersionStrategy::getVersion
   * @dataProvider dataProviderGetVersion
   *
   * @throws ReflectionException
   */
  public function testGetVersion(string $path, string $expected): void
  {
    $this->mockProperty(VersionStrategy::class, $this->object, 'app_version', '1.2.3');
    $this->assertEquals($expected, $this->object->getVersion($path));
  }

  public function dataProviderGetVersion(): array
  {
    return [
      'no parameters in path' => [
        'path' => '/app',
        'expected' => '?v=1.2.3',
      ],
      'parameters in path' => [
        'path' => '/app?color="blue"',
        'expected' => '&v=1.2.3',
      ],
    ];
  }

  /**
   * @group unit
   * @small
   * @covers       \App\Utils\VersionStrategy::getVersion
   */
  public function testApplyVersion(): void
  {
    $path = '/app';
    $this->object = $this->getMockBuilder(VersionStrategy::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getVersion'])
      ->getMockForAbstractClass()
    ;
    $this->object->method('getVersion')->willReturn('?v=1.2.3');
    $expected = '/app?v=1.2.3';
    $this->assertEquals($expected, $this->object->applyVersion($path));
  }
}
