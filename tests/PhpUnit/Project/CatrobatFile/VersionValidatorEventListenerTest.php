<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\VersionValidatorEventListener;
use App\Project\Event\ProjectBeforeInsertEvent;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(VersionValidatorEventListener::class)]
class VersionValidatorEventListenerTest extends TestCase
{
  private VersionValidatorEventListener $version_validator;

  #[\Override]
  protected function setUp(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', BootstrapExtension::$CACHE_DIR.'base/');

    $this->version_validator = new VersionValidatorEventListener();
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testChecksIfTheLanguageVersionIsUpToDate(): void
  {
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertNotFalse($xml);
    $xml->header->catrobatLanguageVersion = '0.92';
    $this->version_validator->validate($xml);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testThrowsAnExceptionIfLanguageVersionIsTooOld(): void
  {
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertNotFalse($xml);
    $xml->header->catrobatLanguageVersion = '0.90';
    $this->expectException(InvalidCatrobatFileException::class);
    $this->version_validator->validate($xml);
  }

  public function testRestoreSkipsTheVersionCheck(): void
  {
    $this->version_validator->onProjectBeforeInsert(new ProjectBeforeInsertEvent($this->extractedFileWithLanguageVersion('0.8'), restore: true));
    $this->expectException(InvalidCatrobatFileException::class);
    $this->version_validator->onProjectBeforeInsert(new ProjectBeforeInsertEvent($this->extractedFileWithLanguageVersion('0.8')));
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  private function extractedFileWithLanguageVersion(string $version): ExtractedCatrobatFile
  {
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertNotFalse($xml);
    $xml->header->catrobatLanguageVersion = $version;
    $file = $this->createStub(ExtractedCatrobatFile::class);
    $file->method('getProjectXmlProperties')->willReturn($xml);

    return $file;
  }
}
