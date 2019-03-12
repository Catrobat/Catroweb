<?php

namespace tests\PhpSpec\spec\App\Catrobat\Services;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScreenshotRepositorySpec extends ObjectBehavior
{
  private $screenshot_dir;
  private $screenshot_base_url;
  private $thumbnail_dir;
  private $thumbnail_base_url;
  private $tmp_dir;
  private $tmp_base_url;
  private $filesystem;

  public function let()
  {
    $this->screenshot_dir = __SPEC_CACHE_DIR__ . '/screenshot_repository/';
    $this->thumbnail_dir = __SPEC_CACHE_DIR__ . '/Cache/thumbnail_repository/';
    $this->tmp_dir = __SPEC_CACHE_DIR__ . '/Cache/tmp/';
    $this->screenshot_base_url = 'screenshots/';
    $this->thumbnail_base_url = 'thumbnails/';
    $this->tmp_base_url = 'tmp/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->screenshot_dir);
    $this->filesystem->mkdir($this->thumbnail_dir);
    $this->filesystem->mkdir($this->tmp_dir);

    $this->beConstructedWith($this->screenshot_dir, $this->screenshot_base_url, $this->thumbnail_dir, $this->thumbnail_base_url, $this->tmp_dir, $this->tmp_base_url);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Services\ScreenshotRepository');
  }

  public function it_throws_an_exception_if_given_an_valid_screenshot_directory()
  {
    $this->shouldThrow('App\Catrobat\Exceptions\InvalidStorageDirectoryException')->during('__construct', [__DIR__ . '/invalid_directory/', '', $this->thumbnail_dir, '', $this->tmp_dir, '']);
  }

  public function it_throws_an_exception_if_given_an_valid_thumbnail_directory()
  {
    $this->shouldThrow('App\Catrobat\Exceptions\InvalidStorageDirectoryException')->during('__construct', [$this->screenshot_dir, '', __DIR__ . '/invalid_directory/', '', $this->tmp_dir, '']);
  }

  public function it_stores_a_screenshot()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__ . '/base/automatic_screenshot.png';
    $id = 'test';
    $this->saveProgramAssets($filepath, $id);

    $finder = new Finder();
    expect($finder->files()->in($this->screenshot_dir)->count())->toBe(1);
  }

  public function it_generates_a_thumbnail()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__ . '/base/automatic_screenshot.png';
    $id = 'test';
    $this->saveProgramAssets($filepath, $id);

    $finder = new Finder();
    expect($finder->files()->in($this->thumbnail_dir)->count())->toBe(1);
  }

  public function it_returns_the_url_of_a_screenshot()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__ . '/base/automatic_screenshot.png';
    $id = 'test';
    $this->saveProgramAssets($filepath, $id);
    $this->getScreenshotWebPath($id)->shouldBe($this->screenshot_base_url . 'screen_test.png');
  }

  public function it_returns_the_url_of_a_thumbnail()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__ . '/base/automatic_screenshot.png';
    $id = 'test';
    $this->saveProgramAssets($filepath, $id);
    $this->getThumbnailWebPath($id)->shouldBe($this->thumbnail_base_url . 'screen_test.png');
  }

  public function it_deletes_all_temporary_files_from_upload_process()
  {
    $this->filesystem->touch($this->tmp_dir . 'tmp_file.txt');
    $finder = new Finder();
    expect($finder->files()->in($this->tmp_dir)->count())->toBe(1);
    $this->deleteTempFiles();
    expect($finder->files()->in($this->tmp_dir)->count())->toBe(0);
  }

  public function letgo()
  {
    $this->filesystem->remove($this->screenshot_dir);
    $this->filesystem->remove($this->thumbnail_dir);
  }
}
