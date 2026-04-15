<?php

declare(strict_types=1);

namespace Tests\PhpUnit\DB\Entity\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ProjectAssetMapping::class)]
class ProjectAssetMappingTest extends TestCase
{
  #[Group('unit')]
  public function testConstructorSetsAllFields(): void
  {
    $project = $this->createStub(Project::class);
    $asset = new ProjectAsset('abc123', 500, 'audio/wav', 'ab/c1/abc123');

    $mapping = new ProjectAssetMapping($project, $asset, 'click.wav', 'sounds/click.wav');

    $this->assertNull($mapping->getId());
    $this->assertSame($project, $mapping->getProject());
    $this->assertSame($asset, $mapping->getAsset());
    $this->assertSame('click.wav', $mapping->getOriginalFilename());
    $this->assertSame('sounds/click.wav', $mapping->getPathInZip());
  }

  #[Group('unit')]
  public function testMappingWithNestedScenePath(): void
  {
    $project = $this->createStub(Project::class);
    $asset = new ProjectAsset('def456', 1024, 'image/png', 'de/f4/def456');

    $mapping = new ProjectAssetMapping($project, $asset, 'cat.png', 'Scene1/images/cat.png');

    $this->assertSame('cat.png', $mapping->getOriginalFilename());
    $this->assertSame('Scene1/images/cat.png', $mapping->getPathInZip());
  }
}
