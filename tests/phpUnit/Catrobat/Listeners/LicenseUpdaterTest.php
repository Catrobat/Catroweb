<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\LicenseUpdater;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\Hook\ClearCacheHook;

/**
 * @internal
 * @coversNothing
 */
class LicenseUpdaterTest extends TestCase
{
  private LicenseUpdater $license_updater;

  protected function setUp(): void
  {
    $this->license_updater = new LicenseUpdater();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(LicenseUpdater::class, $this->license_updater);
  }

  public function testSetsMediaLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(ClearCacheHook::$GENERATED_FIXTURES_DIR.'base', ClearCacheHook::$CACHE_DIR.'base');

    $xml = simplexml_load_file(ClearCacheHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->mediaLicense, '');

    $file = new ExtractedCatrobatFile(ClearCacheHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file('tests/testdata/Cache/base/code.xml');

    Assert::assertEquals($xml->header->mediaLicense, 'https://developer.catrobat.org/ccbysa_v4');
  }

  public function testSetsProgramLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(ClearCacheHook::$GENERATED_FIXTURES_DIR.'base', ClearCacheHook::$CACHE_DIR.'base');

    $xml = simplexml_load_file(ClearCacheHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->programLicense, '');

    $file = new ExtractedCatrobatFile(ClearCacheHook::$CACHE_DIR.'/base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file(ClearCacheHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->programLicense, 'https://developer.catrobat.org/agpl_v3');
  }
}
