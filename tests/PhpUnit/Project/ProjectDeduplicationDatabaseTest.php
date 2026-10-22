<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\DB\EntityRepository\Project\ProjectAssetRepository;
use App\Project\ProjectDeduplicationService;
use App\Storage\ContentAddressableStore;
use App\Storage\FileHelper;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Deduplication against the real database: its collation and unique keys are what bit uploads.
 *
 * @internal
 */
#[CoversClass(ProjectDeduplicationService::class)]
final class ProjectDeduplicationDatabaseTest extends KernelTestCase
{
  private string $dir;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $this->dir = sys_get_temp_dir().'/dedup_db_test_'.uniqid().'/'; // not the cache dir: it may sit on a case-insensitive file system
  }

  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->dir);
    parent::tearDown();
  }

  public function testFilesDifferingOnlyInCaseAndRepeatedContentAreAllMapped(): void
  {
    $container = self::getContainer();
    $entity_manager = $container->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $fixtures = $container->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $fixtures);
    $project = $fixtures->insertProject(['name' => 'dedup '.uniqid()]);

    // Pocket Code file names are case-sensitive; B.png and b.png are two files. Same content twice in one upload.
    $same = 'same '.uniqid();
    $files = ['Scene/images/B.png' => $same, 'Scene/images/b.png' => $same, 'Scene/sounds/b.wav' => 'other '.uniqid()];
    foreach ($files as $path => $content) {
      @mkdir(\dirname($this->dir.'extract/'.$path), 0o777, true);
      file_put_contents($this->dir.'extract/'.$path, $content);
    }

    $assets = $entity_manager->getRepository(ProjectAsset::class);
    $this->assertInstanceOf(ProjectAssetRepository::class, $assets);
    $mappings = $entity_manager->getRepository(ProjectAssetMapping::class);
    $this->assertInstanceOf(ProjectAssetMappingRepository::class, $mappings);
    $service = new ProjectDeduplicationService(
      new ContentAddressableStore($this->dir.'store/'),
      $assets,
      $mappings,
      $entity_manager,
      new NullLogger(),
    );
    $service->deduplicateProject($project, $this->dir.'extract');

    $this->assertTrue($entity_manager->isOpen());
    $entity_manager->clear();
    $this->assertCount(3, $mappings->findBy(['project' => $project->getId()]));
    $asset = $entity_manager->find(ProjectAsset::class, hash('sha256', $same));
    $this->assertSame(2, $asset?->getReferenceCount());
  }
}
