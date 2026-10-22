<?php

declare(strict_types=1);

namespace Tests\PhpUnit\DB\Entity\Project;

use App\DB\Entity\Project\ProjectAsset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ProjectAsset::class)]
class ProjectAssetTest extends TestCase
{
  #[Group('unit')]
  public function testConstructorSetsAllFields(): void
  {
    $hash = hash('sha256', 'test content');
    $asset = new ProjectAsset($hash, 1024, 'image/png', 'ab/cd/'.$hash);

    $this->assertSame($hash, $asset->getHash());
    $this->assertSame(1024, $asset->getSize());
    $this->assertSame('image/png', $asset->getMimeType());
    $this->assertSame('ab/cd/'.$hash, $asset->getStoragePath());
  }

  #[Group('unit')]
  public function testCreatedAtIsSetOnConstruction(): void
  {
    $before = new \DateTimeImmutable();
    $asset = new ProjectAsset('abc123', 100, 'image/png', 'ab/c1/abc123');
    $after = new \DateTimeImmutable();

    $this->assertGreaterThanOrEqual($before, $asset->getCreatedAt());
    $this->assertLessThanOrEqual($after, $asset->getCreatedAt());
  }
}
