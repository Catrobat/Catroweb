<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\LicenseUpdater;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\LicenseUpdater
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
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base', RefreshTestEnvHook::$CACHE_DIR.'base');

    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->mediaLicense, '');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file('tests/testdata/Cache/base/code.xml');

    Assert::assertEquals($xml->header->mediaLicense, 'https://developer.catrobat.org/ccbysa_v4');
  }

  public function testSetsProgramLicense(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base', RefreshTestEnvHook::$CACHE_DIR.'base');

    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->programLicense, '');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'/base/', '/webpath', 'hash');
    $this->license_updater->update($file);
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->programLicense, 'https://developer.catrobat.org/agpl_v3');
  }
}
