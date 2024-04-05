<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
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
    $this->catrobat_file_extractor = new CatrobatFileExtractor(BootstrapExtension::$CACHE_DIR, '/webpath');
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(CatrobatFileExtractor::class, $this->catrobat_file_extractor);
  }

  public function testThrowsAnExceptionIfGivenAnValidExtractionDirectory(): void
  {
    $this->expectException(\Exception::class);
    $this->catrobat_file_extractor = new CatrobatFileExtractor(__DIR__.'invalid_directory/', '');
  }

  /**
   * @throws \Exception
   */
  public function testExtractsAValidFile(): void
  {
    $valid_catrobat_file = new File(BootstrapExtension::$FIXTURES_DIR.'/test.catrobat');
    $extracted_file = $this->catrobat_file_extractor->extract($valid_catrobat_file);
    $this->assertInstanceOf(ExtractedCatrobatFile::class, $extracted_file);
  }

  /**
   * @throws \Exception
   */
  public function testThrowsAnExceptionWhileExtractingAnInvalidFile(): void
  {
    $invalid_catrobat_file = new File(BootstrapExtension::$FIXTURES_DIR.'/invalid_archive.catrobat');
    $this->expectException(InvalidCatrobatFileException::class);
    $this->catrobat_file_extractor->extract($invalid_catrobat_file);
  }
}
