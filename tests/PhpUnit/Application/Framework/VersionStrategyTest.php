<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Framework;

use App\Application\Framework\VersionStrategy;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

/**
 * @internal
 *
 * @coversDefaultClass \App\Application\Framework\VersionStrategy
 */
class VersionStrategyTest extends DefaultTestCase
{
  protected MockObject|VersionStrategy $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(VersionStrategy::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(VersionStrategy::class));
    $this->assertInstanceOf(VersionStrategy::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(VersionStrategyInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new VersionStrategy('1.2.3');
    $this->assertInstanceOf(VersionStrategy::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Application\Framework\VersionStrategy::getVersion
   *
   * @throws \ReflectionException
   */
  #[DataProvider('provideVersionData')]
  public function testGetVersion(string $path, string $expected): void
  {
    $this->mockProperty(VersionStrategy::class, $this->object, 'app_version', '1.2.3');
    $this->assertEquals($expected, $this->object->getVersion($path));
  }

  public static function provideVersionData(): array
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
   *
   * @small
   *
   * @covers       \App\Application\Framework\VersionStrategy::getVersion
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
