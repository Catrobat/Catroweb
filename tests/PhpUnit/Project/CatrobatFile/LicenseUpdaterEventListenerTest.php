<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\LicenseUpdaterEventListener;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(LicenseUpdaterEventListener::class)]
class LicenseUpdaterEventListenerTest extends TestCase
{
  private LicenseUpdaterEventListener $license_updater;

  #[\Override]
  protected function setUp(): void
  {
    $this->license_updater = new LicenseUpdaterEventListener();
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::emptyDirectory(BootstrapExtension::$CACHE_DIR);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(LicenseUpdaterEventListener::class, $this->license_updater);
  }

  public function testSetsMediaLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base', BootstrapExtension::$CACHE_DIR.'base');

    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals('', $xml->header->mediaLicense);

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals('https://developer.catrobat.org/ccbysa_v4', $xml->header->mediaLicense);
  }

  public function testSetsProgramLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base', BootstrapExtension::$CACHE_DIR.'base');

    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals('', $xml->header->programLicense);

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals('https://developer.catrobat.org/agpl_v3', $xml->header->programLicense);
  }
}
