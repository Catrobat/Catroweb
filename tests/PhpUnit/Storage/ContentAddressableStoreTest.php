<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage;

use App\Storage\ContentAddressableStore;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(ContentAddressableStore::class)]
class ContentAddressableStoreTest extends TestCase
{
  private string $assets_dir;

  private ContentAddressableStore $store;

  private Filesystem $filesystem;

  #[\Override]
  protected function setUp(): void
  {
    $this->assets_dir = BootstrapExtension::$CACHE_DIR.'cas_test/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->assets_dir);
    $this->store = new ContentAddressableStore($this->assets_dir);
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->assets_dir);
  }

  #[Group('unit')]
  public function testHashFileReturnsCorrectSha256(): void
  {
    $file = $this->createTempFile('hello world');
    $expected = hash('sha256', 'hello world');

    $this->assertSame($expected, $this->store->hashFile($file));
  }

  #[Group('unit')]
  public function testStoreFileReturnsCorrectRelativePath(): void
  {
    $file = $this->createTempFile('test content');
    $hash = hash('sha256', 'test content');
    $expected_path = substr($hash, 0, 2).'/'.substr($hash, 2, 2).'/'.$hash;

    $relative_path = $this->store->store($file, $hash);

    $this->assertSame($expected_path, $relative_path);
  }

  #[Group('unit')]
  public function testStoreFileCreatesCorrectSubdirectoryStructure(): void
  {
    $file = $this->createTempFile('structure test');
    $hash = hash('sha256', 'structure test');

    $this->store->store($file, $hash);

    $expected_absolute = $this->assets_dir.substr($hash, 0, 2).'/'.substr($hash, 2, 2).'/'.$hash;
    $this->assertFileExists($expected_absolute);
    $this->assertSame('structure test', file_get_contents($expected_absolute));
  }

  #[Group('unit')]
  public function testStoreSameFileTwiceDoesNotDuplicate(): void
  {
    $content = 'duplicate content';
    $file1 = $this->createTempFile($content);
    $file2 = $this->createTempFile($content);
    $hash = hash('sha256', $content);

    $path1 = $this->store->store($file1, $hash);
    $path2 = $this->store->store($file2, $hash);

    $this->assertSame($path1, $path2);

    // Verify only one file on disk in the hash subdirectory
    $absolute = $this->assets_dir.$path1;
    $this->assertFileExists($absolute);
    $this->assertSame($content, file_get_contents($absolute));
  }

  #[Group('unit')]
  public function testStoreDifferentFilesCreatesDifferentEntries(): void
  {
    $file1 = $this->createTempFile('content A');
    $file2 = $this->createTempFile('content B');
    $hash1 = hash('sha256', 'content A');
    $hash2 = hash('sha256', 'content B');

    $path1 = $this->store->store($file1, $hash1);
    $path2 = $this->store->store($file2, $hash2);

    $this->assertNotSame($path1, $path2);
    $this->assertFileExists($this->assets_dir.$path1);
    $this->assertFileExists($this->assets_dir.$path2);
  }

  #[Group('unit')]
  public function testGetAbsolutePathReturnsPathForExistingHash(): void
  {
    $content = 'retrieve test';
    $file = $this->createTempFile($content);
    $hash = hash('sha256', $content);

    $this->store->store($file, $hash);

    $absolute_path = $this->store->getAbsolutePath($hash);

    $this->assertNotNull($absolute_path);
    $this->assertFileExists($absolute_path);
    $this->assertSame($content, file_get_contents($absolute_path));
  }

  #[Group('unit')]
  public function testGetAbsolutePathReturnsNullForMissingHash(): void
  {
    $hash = hash('sha256', 'nonexistent');

    $this->assertNull($this->store->getAbsolutePath($hash));
  }

  #[Group('unit')]
  public function testGetAbsolutePathFromRelative(): void
  {
    $relative = 'ab/cd/abcdef1234567890';

    $absolute = $this->store->getAbsolutePathFromRelative($relative);

    $this->assertSame($this->assets_dir.$relative, $absolute);
  }

  #[Group('unit')]
  public function testDeleteRemovesFileFromDisk(): void
  {
    $content = 'delete me';
    $file = $this->createTempFile($content);
    $hash = hash('sha256', $content);

    $this->store->store($file, $hash);
    $this->assertTrue($this->store->exists($hash));

    $result = $this->store->delete($hash);

    $this->assertTrue($result);
    $this->assertFalse($this->store->exists($hash));
  }

  #[Group('unit')]
  public function testDeleteCleansUpEmptyParentDirectories(): void
  {
    $content = 'cleanup test';
    $file = $this->createTempFile($content);
    $hash = hash('sha256', $content);

    $relative_path = $this->store->store($file, $hash);
    $absolute_path = $this->assets_dir.$relative_path;

    // Verify parent dirs exist before delete
    $parent = dirname($absolute_path);
    $grandparent = dirname($parent);
    $this->assertDirectoryExists($parent);
    $this->assertDirectoryExists($grandparent);

    $this->store->delete($hash);

    // Empty parent directories should be cleaned up
    $this->assertDirectoryDoesNotExist($parent);
    $this->assertDirectoryDoesNotExist($grandparent);
  }

  #[Group('unit')]
  public function testDeleteNonExistentHashReturnsFalse(): void
  {
    $hash = hash('sha256', 'never stored');

    $result = $this->store->delete($hash);

    $this->assertFalse($result);
  }

  #[Group('unit')]
  public function testExistsReturnsTrueForStoredFile(): void
  {
    $file = $this->createTempFile('exists check');
    $hash = hash('sha256', 'exists check');

    $this->store->store($file, $hash);

    $this->assertTrue($this->store->exists($hash));
  }

  #[Group('unit')]
  public function testExistsReturnsFalseForMissingHash(): void
  {
    $this->assertFalse($this->store->exists(hash('sha256', 'missing')));
  }

  #[Group('unit')]
  public function testStoreEmptyFile(): void
  {
    $file = $this->createTempFile('');
    $hash = hash('sha256', '');

    $relative_path = $this->store->store($file, $hash);

    $this->assertFileExists($this->assets_dir.$relative_path);
    $this->assertSame('', file_get_contents($this->assets_dir.$relative_path));
    $this->assertTrue($this->store->exists($hash));
  }

  #[Group('unit')]
  public function testStoreLargeFile(): void
  {
    // Create a 1.5 MB file
    $content = random_bytes(1_500_000);
    $file = $this->createTempFile($content);
    $hash = hash('sha256', $content);

    $relative_path = $this->store->store($file, $hash);

    $stored_path = $this->assets_dir.$relative_path;
    $this->assertFileExists($stored_path);
    $this->assertSame(md5($content), md5_file($stored_path));
  }

  #[Group('unit')]
  public function testStoreIsIdempotentWithSameContent(): void
  {
    $content = 'idempotent test';
    $hash = hash('sha256', $content);

    // Store three times
    $path1 = $this->store->store($this->createTempFile($content), $hash);
    $path2 = $this->store->store($this->createTempFile($content), $hash);
    $path3 = $this->store->store($this->createTempFile($content), $hash);

    $this->assertSame($path1, $path2);
    $this->assertSame($path2, $path3);
    $this->assertSame($content, file_get_contents($this->assets_dir.$path1));
  }

  #[Group('unit')]
  public function testStoreWithRealFixtureFile(): void
  {
    $fixture_file = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png';
    if (!file_exists($fixture_file)) {
      $this->markTestSkipped('Fixture file not found');
    }

    $hash = $this->store->hashFile($fixture_file);
    $relative_path = $this->store->store($fixture_file, $hash);

    $stored_path = $this->assets_dir.$relative_path;
    $this->assertFileExists($stored_path);
    $this->assertSame(md5_file($fixture_file), md5_file($stored_path));
  }

  private function createTempFile(string $content): string
  {
    $path = $this->assets_dir.'tmp_'.bin2hex(random_bytes(8));
    file_put_contents($path, $content);

    return $path;
  }
}
