<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage;

use App\Storage\FileHelper;
use App\Storage\ScreenshotRepository;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 *
 * @covers  \App\Storage\ScreenshotRepository
 */
class ScreenshotRepositoryTest extends TestCase
{
  private string $screenshot_dir;

  private string $screenshot_base_url;

  private string $thumbnail_dir;

  private string $thumbnail_base_url;

  private string $tmp_dir;

  private string $tmp_extract_dir;

  private string $tmp_zip_dir;

  private Filesystem $filesystem;

  private ScreenshotRepository $screenshot_repository;

  private string $project_id;

  protected function setUp(): void
  {
    $this->screenshot_dir = BootstrapExtension::$CACHE_DIR.'screenshot_repository/';
    $this->thumbnail_dir = BootstrapExtension::$CACHE_DIR.'thumbnail_repository/';
    $this->tmp_dir = BootstrapExtension::$CACHE_DIR.'tmp/';
    $this->tmp_extract_dir = BootstrapExtension::$CACHE_DIR.'extract/';
    $this->tmp_zip_dir = BootstrapExtension::$CACHE_DIR.'zips/';
    $this->screenshot_base_url = 'screenshots/';
    $this->thumbnail_base_url = 'thumbnails/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->screenshot_dir);
    $this->filesystem->mkdir($this->thumbnail_dir);
    $this->filesystem->mkdir($this->tmp_dir);
    $this->filesystem->mkdir($this->tmp_extract_dir);
    $this->filesystem->mkdir($this->tmp_zip_dir);

    $this->project_id = 'test_id';
    $this->filesystem->mkdir($this->screenshot_dir.$this->project_id);
    $this->filesystem->mkdir($this->thumbnail_dir.$this->project_id);
    $this->filesystem->mkdir($this->tmp_dir.$this->project_id);
    $this->filesystem->mkdir($this->tmp_extract_dir.$this->project_id);
    $this->filesystem->mkdir($this->tmp_zip_dir.$this->project_id);

    $this->screenshot_repository = new ScreenshotRepository(new ParameterBag([
      'catrobat.screenshot.dir' => $this->screenshot_dir,
      'catrobat.screenshot.path' => $this->screenshot_base_url,
      'catrobat.thumbnail.dir' => $this->thumbnail_dir,
      'catrobat.thumbnail.path' => $this->thumbnail_base_url,
      'catrobat.upload.temp.dir' => $this->tmp_dir,
      'catrobat.file.extract.dir' => $this->tmp_extract_dir,
      'catrobat.file.storage.dir' => $this->tmp_zip_dir,
    ]));
  }

  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->screenshot_dir);
    FileHelper::removeDirectory($this->thumbnail_dir);
    FileHelper::removeDirectory($this->tmp_dir);
    FileHelper::removeDirectory($this->tmp_extract_dir);
    FileHelper::removeDirectory($this->tmp_zip_dir);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ScreenshotRepository::class, $this->screenshot_repository);
  }

  public function testThrowsAnExceptionIfGivenAnInvalidScreenshotDirectory(): void
  {
    $this->expectException(\Exception::class);
    $this->screenshot_repository->__construct(
      new ParameterBag([
        'catrobat.screenshot.dir' => __DIR__.'/invalid_directory/',
        'catrobat.screenshot.path' => $this->screenshot_base_url,
        'catrobat.thumbnail.dir' => $this->thumbnail_dir,
        'catrobat.thumbnail.path' => $this->thumbnail_base_url,
        'catrobat.upload.temp.dir' => $this->tmp_dir,
        'catrobat.file.extract.dir' => $this->tmp_extract_dir,
        'catrobat.file.storage.dir' => $this->tmp_zip_dir,
      ])
    );
  }

  public function testThrowsAnExceptionIfGivenAnInvalidThumbnailDirectory(): void
  {
    $this->expectException(\Exception::class);
    $this->screenshot_repository->__construct(
      new ParameterBag([
        'catrobat.screenshot.dir' => $this->screenshot_dir,
        'catrobat.screenshot.path' => $this->screenshot_base_url,
        'catrobat.thumbnail.dir' => __DIR__.'/invalid_directory/',
        'catrobat.thumbnail.path' => $this->thumbnail_base_url,
        'catrobat.upload.temp.dir' => $this->tmp_dir,
        'catrobat.file.extract.dir' => $this->tmp_extract_dir,
        'catrobat.file.storage.dir' => $this->tmp_zip_dir,
      ])
    );
  }

  /**
   * @throws \ImagickException
   */
  public function testStoresAScreenshot(): void
  {
    $filepath = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    Assert::assertFileDoesNotExist($this->screenshot_dir.'screen_'.$this->project_id.'.png');
    $this->screenshot_repository->saveProjectAssets($filepath, $this->project_id);
    Assert::assertFileExists($this->screenshot_dir.'screen_'.$this->project_id.'.png');
  }

  /**
   * @throws \ImagickException
   */
  public function testOverwriteScreenshot(): void
  {
    $filepath = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    Assert::assertFileDoesNotExist($this->tmp_extract_dir.'/'.$this->project_id.'/manual_screenshot.png');
    $this->screenshot_repository->saveProjectAssets($filepath, $this->project_id);
    Assert::assertFileExists($this->tmp_extract_dir.'/'.$this->project_id.'/manual_screenshot.png');
  }

  /**
   * @throws \ImagickException
   */
  public function testGeneratesAThumbnail(): void
  {
    $filepath = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    Assert::assertFileDoesNotExist($this->thumbnail_dir.'screen_'.$this->project_id.'.png');
    $this->screenshot_repository->saveProjectAssets($filepath, $this->project_id);
    Assert::assertFileExists($this->thumbnail_dir.'screen_'.$this->project_id.'.png');
  }

  /**
   * @throws \ImagickException
   */
  public function testReturnsTheUrlOfAScreenshot(): void
  {
    $filepath = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    $this->screenshot_repository->saveProjectAssets($filepath, $this->project_id);
    $web_path = $this->screenshot_repository->getScreenshotWebPath($this->project_id);
    $this->assertStringStartsWith($this->screenshot_base_url.'screen_'.$this->project_id.'.png', $web_path);
    $this->assertMatchesRegularExpression('/\?t=\d+$/', $web_path);
  }

  /**
   * @throws \ImagickException
   */
  public function testReturnsTheUrlOfAThumbnail(): void
  {
    $filepath = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png';
    $this->screenshot_repository->saveProjectAssets($filepath, $this->project_id);
    $web_path = $this->screenshot_repository->getThumbnailWebPath($this->project_id);
    $this->assertStringStartsWith($this->thumbnail_base_url.'screen_'.$this->project_id.'.png', $web_path);
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
