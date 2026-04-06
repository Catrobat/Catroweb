<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\ProjectDeduplicationService;
use App\Storage\ContentAddressableStore;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(ProjectDeduplicationService::class)]
class ProjectDeduplicationServiceTest extends TestCase
{
  private Stub&ContentAddressableStore $store;

  private Stub&ProjectAssetRepository $asset_repository;

  private Stub&ProjectAssetMappingRepository $mapping_repository;

  private Stub&EntityManagerInterface $entity_manager;

  private Stub&LoggerInterface $logger;

  private string $test_extract_dir;

  #[\Override]
  protected function setUp(): void
  {
    $this->store = $this->createStub(ContentAddressableStore::class);
    $this->asset_repository = $this->createStub(ProjectAssetRepository::class);
    $this->mapping_repository = $this->createStub(ProjectAssetMappingRepository::class);
    $this->entity_manager = $this->createStub(EntityManagerInterface::class);
    $this->logger = $this->createStub(LoggerInterface::class);

    $this->test_extract_dir = BootstrapExtension::$CACHE_DIR.'dedup_test/';
    $filesystem = new Filesystem();
    $filesystem->mkdir($this->test_extract_dir);
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function tearDown(): void
  {
    if (is_dir($this->test_extract_dir)) {
      FileHelper::removeDirectory($this->test_extract_dir);
    }
  }

  #[Group('unit')]
  public function testDeduplicateProjectHashesAllImageAndSoundAssets(): void
  {
    $extract_dir = $this->createProjectStructure([
      'images/sprite1.png' => 'image content 1',
      'images/sprite2.png' => 'image content 2',
      'sounds/click.wav' => 'sound content 1',
    ]);

    $project = $this->createProjectStub();

    $store = $this->createMock(ContentAddressableStore::class);

    // Expect hashFile called for each asset (3 times)
    $store->expects($this->exactly(3))
      ->method('hashFile')
      ->willReturnCallback(fn (string $path) => hash('sha256', (string) file_get_contents($path)))
    ;

    // Expect store called for each new asset (3 times, all new)
    $store->expects($this->exactly(3))
      ->method('store')
      ->willReturn('ab/cd/fakehash')
    ;

    $this->asset_repository->method('findByHash')->willReturn(null);

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $this->entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);
  }

  #[Group('unit')]
  public function testDeduplicateProjectCreatesEntityForNewAssets(): void
  {
    $extract_dir = $this->createProjectStructure([
      'images/sprite.png' => 'new asset content',
    ]);

    $project = $this->createProjectStub();

    $this->store->method('hashFile')->willReturn(hash('sha256', 'new asset content'));
    $this->store->method('store')->willReturn('ab/cd/hash');
    $this->asset_repository->method('findByHash')->willReturn(null);

    $persisted = [];
    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects($this->exactly(2)) // 1 ProjectAsset + 1 ProjectAssetMapping
      ->method('persist')
      ->willReturnCallback(function (object $entity) use (&$persisted): void {
        $persisted[] = $entity::class;
      })
    ;

    $service = new ProjectDeduplicationService(
      $this->store,
      $this->asset_repository,
      $this->mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);

    $this->assertContains(ProjectAsset::class, $persisted);
    $this->assertContains(ProjectAssetMapping::class, $persisted);
  }

  #[Group('unit')]
  public function testDeduplicateProjectIncrementsRefCountForExistingAsset(): void
  {
    $extract_dir = $this->createProjectStructure([
      'images/shared.png' => 'shared content',
    ]);

    $project = $this->createProjectStub();
    $hash = hash('sha256', 'shared content');

    $existing_asset = new ProjectAsset($hash, 14, 'image/png', 'ab/cd/'.$hash);

    $store = $this->createMock(ContentAddressableStore::class);
    $store->expects($this->once())
      ->method('hashFile')
      ->willReturn($hash)
    ;

    // Store should NOT be called — asset already exists
    $store->expects($this->never())->method('store');

    $this->asset_repository->method('findByHash')->willReturn($existing_asset);

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    // Only mapping persisted (not a new ProjectAsset)
    $entity_manager->expects($this->once())
      ->method('persist')
      ->with($this->isInstanceOf(ProjectAssetMapping::class))
    ;

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);

    // Reference count should have been incremented
    $this->assertSame(1, $existing_asset->getReferenceCount());
  }

  #[Group('unit')]
  public function testDeduplicateProjectSkipsCodeXmlAndMetaFiles(): void
  {
    $extract_dir = $this->createProjectStructure([
      'code.xml' => '<xml>project data</xml>',
      'automatic_screenshot.png' => 'screenshot data',
      '.nomedia' => '',
      'images/sprite.png' => 'real asset',
    ]);

    $project = $this->createProjectStub();

    $store = $this->createMock(ContentAddressableStore::class);
    // Only 1 asset file (images/sprite.png) should be hashed
    $store->expects($this->once())
      ->method('hashFile')
      ->willReturnCallback(fn (string $path) => hash('sha256', (string) file_get_contents($path)))
    ;

    $store->expects($this->once())->method('store')->willReturn('ab/cd/hash');
    $this->asset_repository->method('findByHash')->willReturn(null);

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $this->entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);
  }

  #[Group('unit')]
  public function testDeduplicateEmptyProjectDoesNothing(): void
  {
    $extract_dir = $this->createProjectStructure([
      'code.xml' => '<xml>empty project</xml>',
    ]);

    $project = $this->createProjectStub();

    $store = $this->createMock(ContentAddressableStore::class);
    $store->expects($this->never())->method('hashFile');
    $store->expects($this->never())->method('store');

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects($this->never())->method('persist');
    $entity_manager->expects($this->never())->method('flush');

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);
  }

  #[Group('unit')]
  public function testDeduplicateProjectWithNestedSceneDirectories(): void
  {
    $extract_dir = $this->createProjectStructure([
      'code.xml' => '<xml>multi-scene</xml>',
      'Scene1/images/cat.png' => 'cat image',
      'Scene1/sounds/meow.wav' => 'meow sound',
      'Scene2/images/dog.png' => 'dog image',
    ]);

    $project = $this->createProjectStub();

    $store = $this->createMock(ContentAddressableStore::class);
    // Expect 3 assets from nested scenes
    $store->expects($this->exactly(3))
      ->method('hashFile')
      ->willReturnCallback(fn (string $path) => hash('sha256', (string) file_get_contents($path)))
    ;

    $store->expects($this->exactly(3))->method('store')->willReturn('ab/cd/hash');
    $this->asset_repository->method('findByHash')->willReturn(null);

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $this->entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);
  }

  #[Group('unit')]
  public function testRemoveProjectMappingsDecrementsRefCounts(): void
  {
    $project_id = 'test-project-uuid';

    $asset1 = new ProjectAsset('hash1', 100, 'image/png', 'ab/cd/hash1');
    $asset1->incrementReferenceCount(); // ref_count = 1
    $asset1->incrementReferenceCount(); // ref_count = 2

    $asset2 = new ProjectAsset('hash2', 200, 'audio/wav', 'ef/gh/hash2');
    $asset2->incrementReferenceCount(); // ref_count = 1

    $mapping1 = $this->createStub(ProjectAssetMapping::class);
    $mapping1->method('getAsset')->willReturn($asset1);

    $mapping2 = $this->createStub(ProjectAssetMapping::class);
    $mapping2->method('getAsset')->willReturn($asset2);

    $mapping_repository = $this->createMock(ProjectAssetMappingRepository::class);
    $mapping_repository->expects($this->once())
      ->method('findByProjectId')
      ->with($project_id)
      ->willReturn([$mapping1, $mapping2])
    ;

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects($this->exactly(2))
      ->method('remove')
      ->with($this->isInstanceOf(ProjectAssetMapping::class))
    ;
    $entity_manager->expects($this->once())->method('flush');

    $service = new ProjectDeduplicationService(
      $this->store,
      $this->asset_repository,
      $mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $service->removeProjectMappings($project_id);

    // asset1: 2 -> 1 (still referenced by another project)
    $this->assertSame(1, $asset1->getReferenceCount());
    // asset2: 1 -> 0 (orphaned)
    $this->assertSame(0, $asset2->getReferenceCount());
  }

  #[Group('unit')]
  public function testGarbageCollectDeletesOrphanedAssets(): void
  {
    $orphan1 = new ProjectAsset('orphan_hash_1', 100, 'image/png', 'ab/cd/orphan1');
    $orphan2 = new ProjectAsset('orphan_hash_2', 200, 'audio/wav', 'ef/gh/orphan2');

    $this->asset_repository->method('findOrphanedAssets')->willReturn([$orphan1, $orphan2]);

    $store = $this->createMock(ContentAddressableStore::class);
    $store->expects($this->exactly(2))
      ->method('delete')
      ->with($this->logicalOr('orphan_hash_1', 'orphan_hash_2'))
    ;

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects($this->exactly(2))
      ->method('remove')
      ->with($this->logicalOr(
        $this->identicalTo($orphan1),
        $this->identicalTo($orphan2),
      ))
    ;
    $entity_manager->expects($this->once())->method('flush');

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $deleted = $service->garbageCollect(100);

    $this->assertSame(2, $deleted);
  }

  #[Group('unit')]
  public function testGarbageCollectWithNoOrphansDoesNotFlush(): void
  {
    $this->asset_repository->method('findOrphanedAssets')->willReturn([]);

    $store = $this->createMock(ContentAddressableStore::class);
    $store->expects($this->never())->method('delete');

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects($this->never())->method('flush');

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $deleted = $service->garbageCollect();

    $this->assertSame(0, $deleted);
  }

  #[Group('unit')]
  public function testDeduplicateProjectWithDuplicateAssetsInSameProject(): void
  {
    // Two files with identical content in the same project
    $extract_dir = $this->createProjectStructure([
      'images/copy1.png' => 'same content',
      'images/copy2.png' => 'same content',
    ]);

    $project = $this->createProjectStub();
    $hash = hash('sha256', 'same content');

    $store = $this->createMock(ContentAddressableStore::class);
    $store->expects($this->exactly(2))
      ->method('hashFile')
      ->willReturn($hash)
    ;

    // Store called once for the first file only
    $store->expects($this->once())->method('store')->willReturn('ab/cd/'.$hash);

    $asset = null;
    $call_count = 0;
    $this->asset_repository->method('findByHash')->willReturnCallback(
      function () use (&$asset, &$call_count) {
        if (0 === $call_count++) {
          return null;
        }

        return $asset;
      }
    );

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    // Track the persisted ProjectAsset so we can return it on second lookup
    $entity_manager->expects($this->exactly(3)) // 1 ProjectAsset + 2 ProjectAssetMapping
      ->method('persist')
      ->willReturnCallback(function (object $entity) use (&$asset): void {
        if ($entity instanceof ProjectAsset) {
          $asset = $entity;
        }
      })
    ;

    $service = new ProjectDeduplicationService(
      $store,
      $this->asset_repository,
      $this->mapping_repository,
      $entity_manager,
      $this->logger,
    );

    $service->deduplicateProject($project, $extract_dir);
  }

  private function createProjectStub(): Stub&Program
  {
    $project = $this->createStub(Program::class);
    $project->method('getId')->willReturn('test-uuid-1234');

    return $project;
  }

  /**
   * Create a temporary project extract directory with the given file structure.
   *
   * @param array<string, string> $files map of relative path => content
   */
  private function createProjectStructure(array $files): string
  {
    $dir = $this->test_extract_dir.'project_'.bin2hex(random_bytes(4)).'/';
    $filesystem = new Filesystem();

    foreach ($files as $path => $content) {
      $full_path = $dir.$path;
      $filesystem->mkdir(dirname($full_path));
      file_put_contents($full_path, $content);
    }

    return $dir;
  }
}
