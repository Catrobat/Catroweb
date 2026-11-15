<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\GarbageCollectOrphanedAssets;
use App\Project\GarbageCollectOrphanedAssetsHandler;
use App\Project\ProjectDeduplicationService;
use App\Storage\ContentAddressableStore;
use App\Storage\FileHelper;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Deduplication against the real database: its collation, unique and foreign keys are what keep it correct.
 *
 * @internal
 */
#[CoversClass(ProjectDeduplicationService::class)]
#[CoversClass(GarbageCollectOrphanedAssetsHandler::class)]
final class ProjectDeduplicationDatabaseTest extends KernelTestCase
{
  private string $dir;

  private EntityManagerInterface $entity_manager;

  private ProjectAssetRepository $assets;

  private ProjectAssetMappingRepository $mappings;

  private ContentAddressableStore $store;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $this->dir = sys_get_temp_dir().'/dedup_db_test_'.uniqid().'/'; // not the cache dir: it may sit on a case-insensitive file system
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $this->entity_manager = $entity_manager;
    $assets = $entity_manager->getRepository(ProjectAsset::class);
    $this->assertInstanceOf(ProjectAssetRepository::class, $assets);
    $this->assets = $assets;
    $mappings = $entity_manager->getRepository(ProjectAssetMapping::class);
    $this->assertInstanceOf(ProjectAssetMappingRepository::class, $mappings);
    $this->mappings = $mappings;
    $this->store = new ContentAddressableStore($this->dir.'store/');
  }

  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->dir);
    parent::tearDown();
  }

  public function testMapsEveryAssetFileOncePerPath(): void
  {
    $project = $this->insertProject();
    // Pocket Code file names are case-sensitive; B.png and b.png are two files. Same content twice in one upload.
    $same = 'same '.uniqid();
    $this->deduplicate($project, [
      'code.xml' => '<program/>',
      'automatic_screenshot.png' => 'screenshot',
      'images/a.png' => 'single scene '.uniqid(),
      'Scene/images/B.png' => $same,
      'Scene/images/b.png' => $same,
      'Scene/sounds/b.wav' => 'other '.uniqid(),
    ]);

    $this->assertTrue($this->entity_manager->isOpen());
    $this->assertCount(4, $this->mappings->findBy(['project' => $project->getId()]));
    $this->assertCount(2, $this->mappings->findBy(['asset' => hash('sha256', $same)]));
    $this->assertTrue($this->store->exists(hash('sha256', $same)));
  }

  public function testGarbageCollectionDeletesOnlyAssetsNoProjectMaps(): void
  {
    $kept = 'kept '.uniqid();
    $dropped = 'dropped '.uniqid();
    $project = $this->insertProject();
    $this->deduplicate($project, ['images/kept.png' => $kept, 'images/dropped.png' => $dropped]);
    $this->service()->removeProjectMappings((string) $project->getId());
    $this->deduplicate($project, ['images/kept.png' => $kept]); // the update no longer has dropped.png

    $deleted = $this->service()->garbageCollectBatch(new \DateTimeImmutable('+1 minute'), PHP_INT_MAX);

    $this->assertGreaterThanOrEqual(1, $deleted['count']);
    $this->assertFalse($deleted['more']);
    $this->assertTrue($this->store->exists(hash('sha256', $kept)));
    $this->assertNotNull($this->entity_manager->find(ProjectAsset::class, hash('sha256', $kept)));
    $this->assertFalse($this->store->exists(hash('sha256', $dropped)));
    $this->entity_manager->clear();
    $this->assertNull($this->entity_manager->find(ProjectAsset::class, hash('sha256', $dropped)));
  }

  public function testGarbageCollectionKeepsAnAssetMappedAfterItWasListed(): void
  {
    $content = 'raced '.uniqid();
    $this->deduplicate($this->insertProject(), ['images/raced.png' => $content]);
    $assets = $this->createStub(ProjectAssetRepository::class);
    $assets->method('findOrphans')->willReturn([hash('sha256', $content) => 1]); // listed as orphan, then mapped

    $deleted = new ProjectDeduplicationService($this->store, $assets, $this->mappings, $this->entity_manager, new NullLogger())->garbageCollectBatch(new \DateTimeImmutable('+1 minute'), 10);

    $this->assertSame(0, $deleted['count']);
    $this->assertTrue($this->store->exists(hash('sha256', $content)));
    $this->assertTrue($this->entity_manager->isOpen());
  }

  public function testTheJobChainsBatchesUntilNoOrphanIsLeftButSparesFreshOnes(): void
  {
    $orphans = ['a '.uniqid(), 'b '.uniqid(), 'c '.uniqid()];
    $fresh = 'fresh '.uniqid(); // created an hour ago: an upload may still be mapping it
    $project = $this->insertProject();
    $this->deduplicate($project, array_combine(['images/a.png', 'images/b.png', 'images/c.png'], $orphans) + ['images/fresh.png' => $fresh]);
    $this->service()->removeProjectMappings((string) $project->getId());
    $this->entity_manager->getConnection()->executeStatement('UPDATE project_asset SET created_at = ? WHERE hash IN (?, ?, ?)', [
      new \DateTimeImmutable('-2 days')->format('Y-m-d H:i:s'), ...array_map(static fn (string $c): string => hash('sha256', $c), $orphans),
    ]);
    $this->entity_manager->getConnection()->executeStatement('UPDATE project_asset SET created_at = ? WHERE hash = ?', [new \DateTimeImmutable('-1 hour')->format('Y-m-d H:i:s'), hash('sha256', $fresh)]);

    $queue = [new GarbageCollectOrphanedAssets()];
    $bus = $this->createStub(MessageBusInterface::class);
    $bus->method('dispatch')->willReturnCallback(static function (object $message) use (&$queue): Envelope {
      $queue[] = $message;

      return new Envelope($message);
    });
    $handler = new GarbageCollectOrphanedAssetsHandler($this->service(), $bus, new NullLogger(), batch_size: 1);

    $batches = 0;
    while (($message = array_shift($queue)) instanceof GarbageCollectOrphanedAssets) { // what the cleanup worker does
      $handler($message);
      ++$batches;
    }

    $this->assertGreaterThanOrEqual(3, $batches);
    foreach ($orphans as $content) {
      $this->assertFalse($this->store->exists(hash('sha256', $content)));
    }
    $this->assertTrue($this->store->exists(hash('sha256', $fresh)), 'the handler\'s grace period spares a just-uploaded asset');
    $this->assertNotNull($this->entity_manager->find(ProjectAsset::class, hash('sha256', $fresh)));
  }

  public function testOneAssetThatCannotBeDeletedDoesNotStopTheBatch(): void
  {
    $orphans = ['x '.uniqid(), 'y '.uniqid()];
    $project = $this->insertProject();
    $this->deduplicate($project, ['images/x.png' => $orphans[0], 'images/y.png' => $orphans[1]]);
    $this->service()->removeProjectMappings((string) $project->getId());
    $hashes = array_map(static fn (string $c): string => hash('sha256', $c), $orphans);
    sort($hashes);
    $store = $this->createStub(ContentAddressableStore::class);
    $store->method('delete')->willReturnCallback(static fn (string $hash): bool => $hash === $hashes[0] ? throw new \RuntimeException('disk error') : true);
    $assets = $this->createStub(ProjectAssetRepository::class);
    $assets->method('findOrphans')->willReturn(array_fill_keys($hashes, 1));

    $batch = new ProjectDeduplicationService($store, $assets, $this->mappings, $this->entity_manager, new NullLogger())->garbageCollectBatch(new \DateTimeImmutable('+1 minute'), 10);

    $this->assertSame(1, $batch['count']);
    $this->assertSame($hashes[1], $batch['last']);
    $this->assertNotNull($this->entity_manager->find(ProjectAsset::class, $hashes[0]), 'rolled back: row and file stay together');
  }

  public function testGarbageCollectionKeepsOrphansInsideTheGracePeriod(): void
  {
    $content = 'just uploaded '.uniqid();
    $project = $this->insertProject();
    $this->deduplicate($project, ['images/new.png' => $content]);
    $this->service()->removeProjectMappings((string) $project->getId());

    $this->service()->garbageCollectBatch(new \DateTimeImmutable('-1 day'), PHP_INT_MAX);

    $this->assertTrue($this->store->exists(hash('sha256', $content)));
    $this->assertNotNull($this->entity_manager->find(ProjectAsset::class, hash('sha256', $content)));
  }

  private function service(): ProjectDeduplicationService
  {
    return new ProjectDeduplicationService($this->store, $this->assets, $this->mappings, $this->entity_manager, new NullLogger());
  }

  private function insertProject(): Project
  {
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $owner = $users->insertUser(['name' => 'dedup'.uniqid()])->getUsername();
    $fixtures = self::getContainer()->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $fixtures);

    return $fixtures->insertProject(['name' => 'dedup '.uniqid(), 'owned by' => $owner]);
  }

  /**
   * @param array<string, string> $files path in zip => content
   */
  private function deduplicate(Project $project, array $files): void
  {
    $extract = $this->dir.'extract_'.uniqid().'/';
    foreach ($files as $path => $content) {
      @mkdir(\dirname($extract.$path), 0o777, true);
      file_put_contents($extract.$path, $content);
    }

    $this->service()->deduplicateProject($project, $extract);
  }
}
