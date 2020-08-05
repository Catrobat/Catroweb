<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\OldCatrobatLanguageVersionException;
use App\Catrobat\Listeners\VersionValidator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\VersionValidator
 */
class VersionValidatorTest extends TestCase
{
  private VersionValidator $version_validator;

  protected function setUp(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', RefreshTestEnvHook::$CACHE_DIR.'/base/');
    $this->version_validator = new VersionValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(VersionValidator::class, $this->version_validator);
  }

  public function testChecksIfTheLanguageVersionIsUpToDate(): void
  {
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertNotNull($xml);
    $xml->header->catrobatLanguageVersion = '0.92';
    $this->version_validator->validate($xml);
  }

  public function testThrowsAnExceptionIfLanguageVersionIsTooOld(): void
  {
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->catrobatLanguageVersion = '0.90';
    $this->expectException(OldCatrobatLanguageVersionException::class);
    $this->version_validator->validate($xml);
  }
}
