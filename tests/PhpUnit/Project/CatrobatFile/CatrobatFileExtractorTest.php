<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 */
#[CoversClass(CatrobatFileExtractor::class)]
class CatrobatFileExtractorTest extends TestCase
{
  private CatrobatFileExtractor $catrobat_file_extractor;

  #[\Override]
  protected function setUp(): void
  {
    $this->catrobat_file_extractor = new CatrobatFileExtractor(BootstrapExtension::$CACHE_DIR, '/webpath', new NullLogger());
  }

  public function testThrowsAnExceptionIfGivenAnValidExtractionDirectory(): void
  {
    $this->expectException(\Exception::class);
    $this->catrobat_file_extractor = new CatrobatFileExtractor(__DIR__.'invalid_directory/', '', new NullLogger());
  }

  /**
   * @throws \Exception
   */
  public function testExtractsAValidFile(): void
  {
    self::expectNotToPerformAssertions();
    $valid_catrobat_file = new File(BootstrapExtension::$FIXTURES_DIR.'/test.catrobat');
    $this->catrobat_file_extractor->extract($valid_catrobat_file);
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

  /**
   * @throws \Exception
   */
  public function testExtractsFileWithNestedDirectory(): void
  {
    $nested_catrobat_file = $this->createNestedCatrobatFile();
    $extracted = $this->catrobat_file_extractor->extract(new File($nested_catrobat_file));
    self::assertFileExists($extracted->getPath().'code.xml');
    unlink($nested_catrobat_file);
  }

  private function createNestedCatrobatFile(): string
  {
    $tmp_file = (string) tempnam(sys_get_temp_dir(), 'catrobat_test_').'.catrobat';
    $zip = new \ZipArchive();
    $zip->open($tmp_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    $zip->addFromString('ProjectName/code.xml', '<program><header><programName>Test</programName><catrobatLanguageVersion>0.99</catrobatLanguageVersion><description></description><notesAndCredits></notesAndCredits><applicationVersion>1.0</applicationVersion><url></url><remixOf></remixOf><tags></tags></header><objectList></objectList></program>');
    $zip->close();

    return $tmp_file;
  }
}
