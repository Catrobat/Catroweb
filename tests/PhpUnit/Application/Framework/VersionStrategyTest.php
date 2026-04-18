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
  #[DataProvider('provideVersionData')]
  #[Group('unit')]
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
      'hashed JS asset skips version' => [
        'path' => '/build/js/8189-3f826f4c1a2b3d4e.js',
        'expected' => '',
      ],
      'hashed CSS asset skips version' => [
        'path' => '/build/css/base_layout-0ade1f1b2c3d4e5f.css',
        'expected' => '',
      ],
      'hashed font asset skips version' => [
        'path' => '/build/fonts/material-icons.0c35d18b.woff2',
        'expected' => '',
      ],
      'non-hashed image keeps version' => [
        'path' => '/resources/featured/featured_9cce88be.avif',
        'expected' => '?v=1.2.3',
      ],
      'non-hashed SVG keeps version' => [
        'path' => '/build/social/download-on-app-store.svg',
        'expected' => '?v=1.2.3',
      ],
    ];
  }

  #[Group('unit')]
  public function testApplyVersion(): void
  {
    $version_strategy = new VersionStrategy('1.2.3');
    $this->assertEquals('/app?v=1.2.3', $version_strategy->applyVersion('/app'));
  }

  #[Group('unit')]
  public function testApplyVersionSkipsHashedAsset(): void
  {
    $version_strategy = new VersionStrategy('1.2.3');
    $path = '/build/js/runtime-537e323c06d3e83a.js';
    $this->assertEquals($path, $version_strategy->applyVersion($path));
  }
}
