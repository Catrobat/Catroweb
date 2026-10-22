<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\ProjectDeduplicationService;
use App\Storage\ContentAddressableStore;
use App\Storage\FileHelper;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Deduplication against the real database: its collation, unique and foreign keys are what keep it correct.
 *
 * @internal
 */
#[CoversClass(ProjectDeduplicationService::class)]
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

    $this->service()->garbageCollect(PHP_INT_MAX);

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
    $assets->method('findOrphanedHashes')->willReturn([hash('sha256', $content)]); // listed as orphan, then mapped

    $deleted = new ProjectDeduplicationService($this->store, $assets, $this->mappings, $this->entity_manager, new NullLogger())->garbageCollect();

    $this->assertSame(0, $deleted);
    $this->assertTrue($this->store->exists(hash('sha256', $content)));
    $this->assertTrue($this->entity_manager->isOpen());
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
