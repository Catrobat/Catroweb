<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\DB\Entity\Project\ProjectAsset;
use App\DB\Entity\Project\ProjectAssetMapping;
use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use App\Project\CatrobatFile\ProjectZipReconstructor;
use App\Storage\ContentAddressableStore;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(ProjectZipReconstructor::class)]
class ProjectZipReconstructorTest extends TestCase
{
  private string $extract_dir;

  private string $zip_dir;

  private string $assets_dir;

  private Stub&ProjectAssetMappingRepository $mapping_repository;

  private Stub&ContentAddressableStore $store;

  private ProjectZipReconstructor $reconstructor;

  private Filesystem $filesystem;

  #[\Override]
  protected function setUp(): void
  {
    $this->extract_dir = BootstrapExtension::$CACHE_DIR.'reconstruct_extract/';
    $this->zip_dir = BootstrapExtension::$CACHE_DIR.'reconstruct_zip/';
    $this->assets_dir = BootstrapExtension::$CACHE_DIR.'reconstruct_assets/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir([$this->extract_dir, $this->zip_dir, $this->assets_dir]);

    $this->mapping_repository = $this->createStub(ProjectAssetMappingRepository::class);
    $this->store = $this->createStub(ContentAddressableStore::class);

    $this->reconstructor = new ProjectZipReconstructor(
      $this->mapping_repository,
      $this->store,
      $this->createStub(LoggerInterface::class),
      $this->extract_dir,
      $this->zip_dir,
    );
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->extract_dir);
    FileHelper::removeDirectory($this->zip_dir);
    FileHelper::removeDirectory($this->assets_dir);
  }

  #[Group('unit')]
  public function testReconstructReturnsCachedZipIfExists(): void
  {
    $project_id = 'cached-project';
    $zip_path = $this->zip_dir.$project_id.'.catrobat';
    file_put_contents($zip_path, 'fake zip content');

    $result = $this->reconstructor->reconstruct($project_id);

    $this->assertSame($zip_path, $result);
  }

  #[Group('unit')]
  public function testReconstructReturnsNullIfExtractedDirMissing(): void
  {
    $result = $this->reconstructor->reconstruct('nonexistent-project');

    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testReconstructCreatesValidZipWithAssets(): void
  {
    $project_id = 'test-reconstruct';
    $project_extract = $this->extract_dir.$project_id;
    $this->filesystem->mkdir($project_extract);

    // Create code.xml in extracted dir
    file_put_contents($project_extract.'/code.xml', '<xml>test project</xml>');

    // Create asset files in the CAS directory
    $image_content = 'PNG image data';
    $sound_content = 'WAV sound data';
    $image_path = $this->assets_dir.'image_file';
    $sound_path = $this->assets_dir.'sound_file';
    file_put_contents($image_path, $image_content);
    file_put_contents($sound_path, $sound_content);

    // Create asset entities
    $image_asset = new ProjectAsset('imagehash', strlen($image_content), 'image/png', 'ab/cd/imagehash');
    $sound_asset = new ProjectAsset('soundhash', strlen($sound_content), 'audio/wav', 'ef/gh/soundhash');

    // Create mapping stubs
    $image_mapping = $this->createStub(ProjectAssetMapping::class);
    $image_mapping->method('getAsset')->willReturn($image_asset);
    $image_mapping->method('getPathInZip')->willReturn('images/sprite.png');

    $sound_mapping = $this->createStub(ProjectAssetMapping::class);
    $sound_mapping->method('getAsset')->willReturn($sound_asset);
    $sound_mapping->method('getPathInZip')->willReturn('sounds/click.wav');

    $this->mapping_repository->method('findByProjectId')
      ->willReturn([$image_mapping, $sound_mapping])
    ;

    $this->store->method('getAbsolutePathFromRelative')
      ->willReturnCallback(fn (string $rel) => match ($rel) {
        'ab/cd/imagehash' => $image_path,
        'ef/gh/soundhash' => $sound_path,
        default => $this->assets_dir.$rel,
      })
    ;

    $result = $this->reconstructor->reconstruct($project_id);

    $this->assertNotNull($result);
    $this->assertFileExists($result);

    // Verify ZIP contents
    $zip = new \ZipArchive();
    $this->assertTrue(true === $zip->open($result));

    $this->assertNotFalse($zip->locateName('code.xml'));
    $this->assertSame('<xml>test project</xml>', $zip->getFromName('code.xml'));

    $this->assertNotFalse($zip->locateName('images/sprite.png'));
    $this->assertSame($image_content, $zip->getFromName('images/sprite.png'));

    $this->assertNotFalse($zip->locateName('sounds/click.wav'));
    $this->assertSame($sound_content, $zip->getFromName('sounds/click.wav'));

    $zip->close();
  }

  #[Group('unit')]
  public function testReconstructFallsBackToExtractedFileWhenAssetMissing(): void
  {
    $project_id = 'fallback-project';
    $project_extract = $this->extract_dir.$project_id;
    $this->filesystem->mkdir($project_extract.'/images');

    file_put_contents($project_extract.'/code.xml', '<xml>fallback</xml>');
    file_put_contents($project_extract.'/images/sprite.png', 'fallback image');

    $asset = new ProjectAsset('missinghash', 14, 'image/png', 'xx/yy/missinghash');
    $mapping = $this->createStub(ProjectAssetMapping::class);
    $mapping->method('getAsset')->willReturn($asset);
    $mapping->method('getPathInZip')->willReturn('images/sprite.png');

    $this->mapping_repository->method('findByProjectId')->willReturn([$mapping]);

    // CAS returns a path that doesn't exist
    $this->store->method('getAbsolutePathFromRelative')
      ->willReturn($this->assets_dir.'nonexistent')
    ;

    $result = $this->reconstructor->reconstruct($project_id);

    $this->assertNotNull($result);

    $zip = new \ZipArchive();
    $zip->open($result);
    // Should fall back to extracted file
    $this->assertSame('fallback image', $zip->getFromName('images/sprite.png'));
    $zip->close();
  }

  #[Group('unit')]
  public function testReconstructIncludesScreenshots(): void
  {
    $project_id = 'screenshot-project';
    $project_extract = $this->extract_dir.$project_id;
    $this->filesystem->mkdir($project_extract.'/images');

    file_put_contents($project_extract.'/code.xml', '<xml>screenshots</xml>');
    file_put_contents($project_extract.'/automatic_screenshot.png', 'auto screenshot');
    file_put_contents($project_extract.'/manual_screenshot.png', 'manual screenshot');

    // Need at least one mapping — reconstruct returns null for empty mappings
    $image_content = 'dummy image';
    $image_path = $this->assets_dir.'dummy_img';
    file_put_contents($image_path, $image_content);

    $asset = new ProjectAsset('dummyhash', strlen($image_content), 'image/png', 'du/mm/dummyhash');
    $mapping = $this->createStub(ProjectAssetMapping::class);
    $mapping->method('getAsset')->willReturn($asset);
    $mapping->method('getPathInZip')->willReturn('images/dummy.png');

    $this->mapping_repository->method('findByProjectId')->willReturn([$mapping]);
    $this->store->method('getAbsolutePathFromRelative')->willReturn($image_path);

    $result = $this->reconstructor->reconstruct($project_id);

    $this->assertNotNull($result);

    $zip = new \ZipArchive();
    $zip->open($result);
    $this->assertNotFalse($zip->locateName('automatic_screenshot.png'));
    $this->assertNotFalse($zip->locateName('manual_screenshot.png'));
    $zip->close();
  }

  #[Group('unit')]
  public function testInvalidateCacheDeletesZipFile(): void
  {
    $project_id = 'invalidate-test';
    $zip_path = $this->zip_dir.$project_id.'.catrobat';
    file_put_contents($zip_path, 'cached zip');

    $this->assertFileExists($zip_path);

    $this->reconstructor->invalidateCache($project_id);

    $this->assertFileDoesNotExist($zip_path);
  }

  #[Group('unit')]
  public function testInvalidateCacheDoesNothingIfNoCache(): void
  {
    $this->expectNotToPerformAssertions();

    // Should not throw
    $this->reconstructor->invalidateCache('no-cache-project');
  }
}
