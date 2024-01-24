<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\LicenseUpdaterEventSubscriber;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\LicenseUpdaterEventSubscriber
 */
class LicenseUpdaterEventSubscriberTest extends TestCase
{
  private LicenseUpdaterEventSubscriber $license_updater;

  protected function setUp(): void
  {
    $this->license_updater = new LicenseUpdaterEventSubscriber();
  }

  public function tearDown(): void
  {
    FileHelper::emptyDirectory(BootstrapExtension::$CACHE_DIR);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(LicenseUpdaterEventSubscriber::class, $this->license_updater);
  }

  public function testSetsMediaLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base', BootstrapExtension::$CACHE_DIR.'base');

    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->mediaLicense, '');

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->mediaLicense, 'https://developer.catrobat.org/ccbysa_v4');
  }

  public function testSetsProjectLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base', BootstrapExtension::$CACHE_DIR.'base');

    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->programLicense, '');

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->programLicense, 'https://developer.catrobat.org/agpl_v3');
  }
}
