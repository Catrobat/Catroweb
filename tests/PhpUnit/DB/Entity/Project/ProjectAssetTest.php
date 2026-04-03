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
    $this->assertSame(0, $asset->getReferenceCount());
    $this->assertInstanceOf(\DateTimeImmutable::class, $asset->getCreatedAt());
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

  #[Group('unit')]
  public function testIncrementReferenceCount(): void
  {
    $asset = new ProjectAsset('hash', 100, 'image/png', 'ha/sh/hash');

    $this->assertSame(0, $asset->getReferenceCount());

    $asset->incrementReferenceCount();
    $this->assertSame(1, $asset->getReferenceCount());

    $asset->incrementReferenceCount();
    $this->assertSame(2, $asset->getReferenceCount());

    $asset->incrementReferenceCount();
    $this->assertSame(3, $asset->getReferenceCount());
  }

  #[Group('unit')]
  public function testDecrementReferenceCount(): void
  {
    $asset = new ProjectAsset('hash', 100, 'image/png', 'ha/sh/hash');
    $asset->incrementReferenceCount();
    $asset->incrementReferenceCount();

    $this->assertSame(2, $asset->getReferenceCount());

    $asset->decrementReferenceCount();
    $this->assertSame(1, $asset->getReferenceCount());

    $asset->decrementReferenceCount();
    $this->assertSame(0, $asset->getReferenceCount());
  }

  #[Group('unit')]
  public function testDecrementReferenceCountDoesNotGoBelowZero(): void
  {
    $asset = new ProjectAsset('hash', 100, 'image/png', 'ha/sh/hash');

    $this->assertSame(0, $asset->getReferenceCount());

    $asset->decrementReferenceCount();
    $this->assertSame(0, $asset->getReferenceCount());

    // Decrement again — still zero
    $asset->decrementReferenceCount();
    $this->assertSame(0, $asset->getReferenceCount());
  }
}
