<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Catrobat\Services\CatrobatFileCompressor;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\Hook\ClearCacheHook;

/**
 * @internal
 * @coversNothing
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
    $this->expectException(InvalidStorageDirectoryException::class);
    $this->catrobat_file_compressor->compress(__DIR__.'/invalid_directory/', ClearCacheHook::$CACHE_DIR.'base/', 'archivename');
  }

  public function testCompressAValidDirectory(): void
  {
    $filesystem = new Filesystem();
    $path_to_file = ClearCacheHook::$GENERATED_FIXTURES_DIR.'base';
    $filesystem->mirror($path_to_file, ClearCacheHook::$CACHE_DIR.'base/');
    $this->catrobat_file_compressor->compress(ClearCacheHook::$CACHE_DIR.'base/', ClearCacheHook::$CACHE_DIR, 'base');
    Assert::assertTrue(is_file(ClearCacheHook::$CACHE_DIR.'base.catrobat'));
  }
}
