<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\CatrobatFileCompressor;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(CatrobatFileCompressor::class)]
class CatrobatFileCompressorTest extends TestCase
{
  private CatrobatFileCompressor $catrobat_file_compressor;

  #[\Override]
  protected function setUp(): void
  {
    $this->catrobat_file_compressor = new CatrobatFileCompressor();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(CatrobatFileCompressor::class, $this->catrobat_file_compressor);
  }

  public function testThrowsAnExceptionIfGivenAnInvalidCompressDirectory(): void
  {
    $this->expectException(\Exception::class);
    $this->catrobat_file_compressor->compress(__DIR__.'/invalid_directory/', BootstrapExtension::$CACHE_DIR.'base/', 'archivename');
  }

  /**
   * @throws \Exception
   */
  public function testCompressAValidDirectory(): void
  {
    $filesystem = new Filesystem();
    $path_to_file = BootstrapExtension::$GENERATED_FIXTURES_DIR.'base';
    $filesystem->mirror($path_to_file, BootstrapExtension::$CACHE_DIR.'base/');
    $this->catrobat_file_compressor->compress(BootstrapExtension::$CACHE_DIR.'base/', BootstrapExtension::$CACHE_DIR, 'base');
    Assert::assertTrue(is_file(BootstrapExtension::$CACHE_DIR.'base.catrobat'));
  }
}
