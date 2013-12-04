<?php

namespace Catrobat\CoreBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScreenshotRepositorySpec extends ObjectBehavior
{
  private $screenshot_dir;
  private $screenshot_base_url;
  private $thumbnail_dir;
  private $thumbnail_base_url;
  private $filesystem;
  
  function let()
  {
    $this->screenshot_dir = __SPEC_CACHE_DIR__."/screenshot_repository/";
    $this->thumbnail_dir = __SPEC_CACHE_DIR__."/Cache/thumbnail_repository/";
    $this->screenshot_base_url = "screenshots/";
    $this->thumbnail_base_url = "thumbnails/";
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->screenshot_dir);
    $this->filesystem->mkdir($this->thumbnail_dir);
    
    $this->beConstructedWith($this->screenshot_dir, $this->screenshot_base_url, $this->thumbnail_dir, $this->thumbnail_base_url);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CoreBundle\Services\ScreenshotRepository');
  }

  function it_throws_an_exception_if_given_an_valid_screenshot_directory()
  {
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidStorageDirectoryException')->during('__construct', array(__DIR__ . "/invalid_directory/", "", $this->thumbnail_dir, ""));
  }

  function it_throws_an_exception_if_given_an_valid_thumbnail_directory()
  {
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidStorageDirectoryException')->during('__construct', array($this->screenshot_dir, "", __DIR__ . "/invalid_directory/", ""));
  }
  
  function it_stores_a_screenshot()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__."/base/automatic_screenshot.png";
    $id = "test";
    $this->saveProjectAssets($filepath, $id);
    
    $finder = new Finder();
    expect($finder->files()->in($this->screenshot_dir)->count())->toBe(1);
  }

  function it_generates_a_thumbnail()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__."/base/automatic_screenshot.png";
    $id = "test";
    $this->saveProjectAssets($filepath, $id);
    
    $finder = new Finder();
    expect($finder->files()->in($this->thumbnail_dir)->count())->toBe(1);
  }

  function it_returns_the_url_of_a_screenshot()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__."/base/automatic_screenshot.png";
    $id = "test";
    $this->saveProjectAssets($filepath, $id);
    $this->getScreenshotWebPath($id)->shouldBe($this->screenshot_base_url . "screen_test.png");
  }

  function it_returns_the_url_of_a_thumbnail()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__."/base/automatic_screenshot.png";
    $id = "test";
    $this->saveProjectAssets($filepath, $id);
    $this->getThumbnailWebPath($id)->shouldBe($this->thumbnail_base_url . "screen_test.png");
  }

  function letgo()
  {
    $this->filesystem->remove($this->screenshot_dir);
    $this->filesystem->remove($this->thumbnail_dir);
  }
}
