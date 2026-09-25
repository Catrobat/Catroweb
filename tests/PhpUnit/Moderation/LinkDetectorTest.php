<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\Moderation\LinkDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LinkDetector::class)]
#[Group('unit')]
class LinkDetectorTest extends TestCase
{
  private LinkDetector $detector;

  #[\Override]
  protected function setUp(): void
  {
    $this->detector = new LinkDetector();
  }

  #[DataProvider('linkProvider')]
  public function testDetectsLink(string $text): void
  {
    $this->assertTrue($this->detector->containsLink($text));
  }

  /**
   * @return array<string, array{string}>
   */
  public static function linkProvider(): array
  {
    return [
      'reported channel link' => ['https://youtube.com/@test24710?si=gnp8zCcpKyAAjnna'],
      'http link' => ['http://example.org'],
      'other scheme' => ['ftp://files.example'],
      'www prefix' => ['www.mychannel'],
      'bare domain' => ['youtube.com/@someone'],
      'short link' => ['youtu.be/abc'],
      'domain inside a name' => ['CoolGamer_twitch.tv'],
      'spaced dot' => ['youtube . com'],
      'upper case' => ['VISIT MYSITE.NET'],
      'zero-width space in domain' => ["youtube\u{200B}.com"],
      'country domain' => ['meinshop.de'],
    ];
  }

  #[DataProvider('noLinkProvider')]
  public function testAllowsTextWithoutLink(?string $text): void
  {
    $this->assertFalse($this->detector->containsLink($text));
  }

  /**
   * @return array<string, array{?string}>
   */
  public static function noLinkProvider(): array
  {
    return [
      'null' => [null],
      'empty' => [''],
      'plain name' => ['Catrobat'],
      'name with dot' => ['max.mustermann'],
      'initial with dot' => ['J.Smith'],
      'tld prefix of a word' => ['sam.coolguy'],
      'version number' => ['Gamer 2.0'],
      'handle without domain' => ['@test24710'],
      'umlauts' => ['Jürgen.Müller'],
    ];
  }
}
