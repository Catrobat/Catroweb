<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Framework;

use App\Application\Framework\VersionStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VersionStrategy::class)]
class VersionStrategyTest extends TestCase
{
  /**
   * @group unit
   *
   * @throws \Exception
   */
  #[DataProvider('provideVersionData')]
  public function testGetVersion(string $path, string $expected): void
  {
    $version_strategy = new VersionStrategy('1.2.3');
    $this->assertEquals($expected, $version_strategy->getVersion($path));
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
   * @throws \Exception
   */
  #[Group('unit')]
  public function testApplyVersion(): void
  {
    $version_strategy = new VersionStrategy('1.2.3');
    $this->assertEquals('/app?v=1.2.3', $version_strategy->applyVersion('/app'));
  }
}
