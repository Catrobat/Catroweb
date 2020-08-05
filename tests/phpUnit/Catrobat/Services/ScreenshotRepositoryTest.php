<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Catrobat\Services\ScreenshotRepository;
use ImagickException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Services\ScreenshotRepository
 */
class ScreenshotRepositoryTest extends TestCase
{
  private string $screenshot_dir;

  private string $screenshot_base_url;

  private string $thumbnail_dir;

  private string $thumbnail_base_url;

  private string $tmp_dir;

  private string $tmp_base_url;

  private Filesystem $filesystem;

  private ScreenshotRepository $screenshot_repository;

  protected function setUp(): void
  {
    $this->screenshot_dir = RefreshTestEnvHook::$CACHE_DIR.'screenshot_repository/';
    $this->thumbnail_dir = RefreshTestEnvHook::$CACHE_DIR.'thumbnail_repository/';
    $this->tmp_dir = RefreshTestEnvHook::$CACHE_DIR.'tmp/';
    $this->screenshot_base_url = 'screenshots/';
    $this->thumbnail_base_url = 'thumbnails/';
    $this->tmp_base_url = 'tmp/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->screenshot_dir);
    $this->filesystem->mkdir($this->thumbnail_dir);
    $this->filesystem->mkdir($this->tmp_dir);

    $this->screenshot_repository = new ScreenshotRepository($this->screenshot_dir, $this->screenshot_base_url, $this->thumbnail_dir, $this->thumbnail_base_url, $this->tmp_dir, $this->tmp_base_url);
  }

  protected function tearDown(): void
  {
    $this->filesystem->remove($this->screenshot_dir);
    $this->filesystem->remove($this->thumbnail_dir);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ScreenshotRepository::class, $this->screenshot_repository);
  }

  public function testThrowsAnExceptionIfGivenAnValidScreenshotDirectory(): void
  {
    $this->expectException(InvalidStorageDirectoryException::class);
    $this->screenshot_repository->__construct(__DIR__.'/invalid_directory/', $this->screenshot_base_url, $this->thumbnail_dir, $this->thumbnail_base_url, $this->tmp_dir, $this->tmp_base_url);
  }

  public function testThrowsAnExceptionIfGivenAnValidThumbnailDirectory(): void
  {
    $this->expectException(InvalidStorageDirectoryException::class);
    $this->screenshot_repository->__construct($this->screenshot_dir, $this->screenshot_base_url, __DIR__.'/invalid_directory/', $this->thumbnail_base_url, $this->tmp_dir, $this->tmp_base_url);
  }

  /**
   * @throws ImagickException
   */
  public function testStoresAScreenshot(): void
  {
    $filepath = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    $id = 'test';
    $this->screenshot_repository->saveProgramAssets($filepath, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->screenshot_dir)->count());
  }

  /**
   * @throws ImagickException
   */
  public function testGeneratesAThumbnail(): void
  {
    $filepath = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    $id = 'test';
    $this->screenshot_repository->saveProgramAssets($filepath, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->thumbnail_dir)->count());
  }

  /**
   * @throws ImagickException
   */
  public function testReturnsTheUrlOfAScreenshot(): void
  {
    $filepath = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    $id = 'test';
    $this->screenshot_repository->saveProgramAssets($filepath, $id);
    $web_path = $this->screenshot_repository->getScreenshotWebPath($id);
    $this->assertStringStartsWith($this->screenshot_base_url.'screen_test.png', $web_path);
    $this->assertMatchesRegularExpression('/\?t=\d+$/', $web_path);
  }

  /**
   * @throws ImagickException
   */
  public function testReturnsTheUrlOfAThumbnail(): void
  {
    $filepath = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    $id = 'test';
    $this->screenshot_repository->saveProgramAssets($filepath, $id);
    $web_path = $this->screenshot_repository->getThumbnailWebPath($id);
    $this->assertStringStartsWith($this->thumbnail_base_url.'screen_test.png', $web_path);
    $this->assertMatchesRegularExpression('/\?t=\d+$/', $web_path);
  }

  public function testDeletesAllTemporaryFilesFromUploadProcess(): void
  {
    $this->filesystem->touch($this->tmp_dir.'tmp_file.txt');
    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->tmp_dir)->count());
    $this->screenshot_repository->deleteTempFiles();
    Assert::assertEquals(0, $finder->files()->in($this->tmp_dir)->count());
  }
}
