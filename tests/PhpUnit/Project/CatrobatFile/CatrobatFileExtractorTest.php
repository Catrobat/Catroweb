<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\System\Testing\PhpUnit\Hook\RefreshTestEnvHook;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\CatrobatFileExtractor
 */
class CatrobatFileExtractorTest extends TestCase
{
  private CatrobatFileExtractor $catrobat_file_extractor;

  protected function setUp(): void
  {
    $this->catrobat_file_extractor = new CatrobatFileExtractor(RefreshTestEnvHook::$CACHE_DIR, '/webpath');
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(CatrobatFileExtractor::class, $this->catrobat_file_extractor);
  }

  public function testThrowsAnExceptionIfGivenAnValidExtractionDirectory(): void
  {
    $this->expectException(Exception::class);
    $this->catrobat_file_extractor = new CatrobatFileExtractor(__DIR__.'invalid_directory/', '');
  }

  /**
   * @throws Exception
   */
  public function testExtractsAValidFile(): void
  {
    $valid_catrobat_file = new File(RefreshTestEnvHook::$FIXTURES_DIR.'/test.catrobat');
    $extracted_file = $this->catrobat_file_extractor->extract($valid_catrobat_file);
    $this->assertInstanceOf(ExtractedCatrobatFile::class, $extracted_file);
  }

  /**
   * @throws Exception
   */
  public function testThrowsAnExceptionWhileExtractingAnInvalidFile(): void
  {
    $invalid_catrobat_file = new File(RefreshTestEnvHook::$FIXTURES_DIR.'/invalid_archive.catrobat');
    $this->expectException(InvalidCatrobatFileException::class);
    $this->catrobat_file_extractor->extract($invalid_catrobat_file);
  }
}
