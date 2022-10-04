<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\CatrobatFileCompressor;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\CatrobatFileCompressor
 */
class CatrobatFileCompressorTest extends TestCase
{
  private CatrobatFileCompressor $catrobat_file_compressor;

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
    $this->expectException(Exception::class);
    $this->catrobat_file_compressor->compress(__DIR__.'/invalid_directory/', RefreshTestEnvHook::$CACHE_DIR.'base/', 'archivename');
  }

  public function testCompressAValidDirectory(): void
  {
    $filesystem = new Filesystem();
    $path_to_file = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base';
    $filesystem->mirror($path_to_file, RefreshTestEnvHook::$CACHE_DIR.'base/');
    $this->catrobat_file_compressor->compress(RefreshTestEnvHook::$CACHE_DIR.'base/', RefreshTestEnvHook::$CACHE_DIR, 'base');
    Assert::assertTrue(is_file(RefreshTestEnvHook::$CACHE_DIR.'base.catrobat'));
  }
}
