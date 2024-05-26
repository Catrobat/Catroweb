<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\VersionValidatorEventSubscriber;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\VersionValidatorEventSubscriber
 */
class VersionValidatorEventSubscriberTest extends TestCase
{
  private VersionValidatorEventSubscriber $version_validator;

  #[\Override]
  protected function setUp(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', BootstrapExtension::$CACHE_DIR.'base/');

    $this->version_validator = new VersionValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(VersionValidatorEventSubscriber::class, $this->version_validator);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testChecksIfTheLanguageVersionIsUpToDate(): void
  {
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.92';
    $this->version_validator->validate($xml);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testThrowsAnExceptionIfLanguageVersionIsTooOld(): void
  {
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.90';
    $this->expectException(InvalidCatrobatFileException::class);
    $this->version_validator->validate($xml);
  }
}
