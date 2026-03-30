<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\Moderation\TextSanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(TextSanitizer::class)]
#[Group('unit')]
class TextSanitizerTest extends TestCase
{
  private TextSanitizer $sanitizer;
  private string $wordListDir;

  #[\Override]
  protected function setUp(): void
  {
    $this->wordListDir = dirname(__DIR__, 3).'/config/moderation/wordlists';
    $requestStack = $this->createStub(RequestStack::class);
    $this->sanitizer = new TextSanitizer($requestStack, $this->wordListDir);
  }

  // --- Null / Empty / Normal ---

  public function testSanitizeReturnsNullForNull(): void
  {
    $this->assertNull($this->sanitizer->sanitizeWithLocale(null));
  }

  public function testSanitizeReturnsEmptyForEmpty(): void
  {
    $this->assertSame('', $this->sanitizer->sanitizeWithLocale(''));
  }

  public function testSanitizePreservesNormalText(): void
  {
    $this->assertSame('Hello, world!', $this->sanitizer->sanitizeWithLocale('Hello, world!'));
  }

  // --- Invisible character removal ---

  #[DataProvider('invisibleCharacterProvider')]
  public function testStripsInvisibleCharacters(string $input, string $expected): void
  {
    $this->assertSame($expected, $this->sanitizer->sanitizeWithLocale($input));
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function invisibleCharacterProvider(): array
  {
    return [
      'zero-width space' => ["hel\u{200B}lo", 'hello'],
      'zero-width non-joiner' => ["hel\u{200C}lo", 'hello'],
      'zero-width joiner' => ["hel\u{200D}lo", 'hello'],
      'soft hyphen' => ["hel\u{00AD}lo", 'hello'],
      'word joiner' => ["hel\u{2060}lo", 'hello'],
      'BOM' => ["\u{FEFF}hello", 'hello'],
      'LTR mark' => ["he\u{200E}llo", 'hello'],
      'RTL mark' => ["he\u{200F}llo", 'hello'],
      'multiple types' => ["\u{FEFF}h\u{200B}e\u{200C}l\u{200D}l\u{00AD}o\u{2060}", 'hello'],
    ];
  }

  // --- Email redaction ---

  public function testRedactsSimpleEmail(): void
  {
    $this->assertSame(
      'Contact me at [contact removed] for info',
      $this->sanitizer->sanitizeWithLocale('Contact me at user@example.com for info')
    );
  }

  public function testRedactsEmailWithSubdomain(): void
  {
    $this->assertSame(
      'Send to [contact removed]',
      $this->sanitizer->sanitizeWithLocale('Send to user@mail.example.co.uk')
    );
  }

  public function testRedactsMultipleEmails(): void
  {
    $this->assertSame(
      '[contact removed] and [contact removed]',
      $this->sanitizer->sanitizeWithLocale('a@b.com and c@d.org')
    );
  }

  // --- Phone number redaction ---

  public function testRedactsInternationalPhoneWithPlus(): void
  {
    $this->assertSame(
      'Call [contact removed] now',
      $this->sanitizer->sanitizeWithLocale('Call +1 234 567 8900 now')
    );
  }

  public function testRedactsInternationalPhoneWith00(): void
  {
    $this->assertSame(
      'Call [contact removed] now',
      $this->sanitizer->sanitizeWithLocale('Call 0044 7911 123456 now')
    );
  }

  public function testRedactsPhoneWithDashes(): void
  {
    $this->assertSame(
      'Call [contact removed]',
      $this->sanitizer->sanitizeWithLocale('Call +49-170-1234567')
    );
  }

  public function testDoesNotRedactShortNumbers(): void
  {
    $this->assertSame(
      'Code is +12345',
      $this->sanitizer->sanitizeWithLocale('Code is +12345')
    );
  }

  // --- Contact link redaction ---

  #[DataProvider('contactLinkProvider')]
  public function testRedactsContactLinks(string $input, string $expected): void
  {
    $this->assertSame($expected, $this->sanitizer->sanitizeWithLocale($input));
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function contactLinkProvider(): array
  {
    return [
      'discord.gg' => ['Join https://discord.gg/abc123 now', 'Join [contact removed] now'],
      'discord.com/invite' => ['Join https://discord.com/invite/abc now', 'Join [contact removed] now'],
      'telegram' => ['Chat at https://t.me/mygroup', 'Chat at [contact removed]'],
      'whatsapp chat' => ['https://chat.whatsapp.com/invite123', '[contact removed]'],
      'wa.me' => ['https://wa.me/1234567890', '[contact removed]'],
      'snapchat' => ['Add me https://snapchat.com/add/myuser', 'Add me [contact removed]'],
      'instagram' => ['Follow https://instagram.com/myuser', 'Follow [contact removed]'],
      'instagr.am' => ['https://instagr.am/myuser', '[contact removed]'],
      'kik' => ['https://kik.me/myuser', '[contact removed]'],
      'tiktok profile' => ['https://tiktok.com/@myuser', '[contact removed]'],
      'tiktok short' => ['https://vm.tiktok.com/abc123', '[contact removed]'],
      'facebook' => ['https://facebook.com/myuser', '[contact removed]'],
      'fb.me' => ['https://fb.me/myuser', '[contact removed]'],
      'messenger' => ['https://m.me/myuser', '[contact removed]'],
      'signal' => ['https://signal.me/#p/abc', '[contact removed]'],
      'skype' => ['https://join.skype.com/abc123', '[contact removed]'],
      'twitter' => ['https://twitter.com/myuser', '[contact removed]'],
      'x.com' => ['https://x.com/myuser', '[contact removed]'],
      'twitch' => ['https://twitch.tv/myuser', '[contact removed]'],
    ];
  }

  // --- URI scheme redaction ---

  #[DataProvider('uriSchemeProvider')]
  public function testRedactsUriSchemes(string $input, string $expected): void
  {
    $this->assertSame($expected, $this->sanitizer->sanitizeWithLocale($input));
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function uriSchemeProvider(): array
  {
    return [
      'skype' => ['skype://user?chat', '[contact removed]'],
      'telegram' => ['tg://resolve?domain=user', '[contact removed]'],
      'whatsapp' => ['whatsapp://send?phone=123', '[contact removed]'],
      'viber' => ['viber://chat?number=123', '[contact removed]'],
      'line' => ['line://ti/p/user', '[contact removed]'],
    ];
  }

  // --- Profanity filtering ---

  public function testFiltersProfanityWord(): void
  {
    $this->assertSame('What the **** is this', $this->sanitizer->sanitizeWithLocale('What the fuck is this'));
  }

  public function testFiltersProfanityCaseInsensitive(): void
  {
    $this->assertSame('What the **** is this', $this->sanitizer->sanitizeWithLocale('What the FUCK is this'));
  }

  public function testDoesNotFilterPartialWordClassic(): void
  {
    $this->assertSame('This is a classic game', $this->sanitizer->sanitizeWithLocale('This is a classic game'));
  }

  public function testDoesNotFilterPartialWordScunthorpe(): void
  {
    $this->assertSame('I live in Scunthorpe', $this->sanitizer->sanitizeWithLocale('I live in Scunthorpe'));
  }

  public function testDoesNotFilterPartialWordAssassinate(): void
  {
    $this->assertSame('assassinate the target', $this->sanitizer->sanitizeWithLocale('assassinate the target'));
  }

  public function testDoesNotFilterPartialWordGrass(): void
  {
    $this->assertSame('The grass is green', $this->sanitizer->sanitizeWithLocale('The grass is green'));
  }

  public function testFiltersMultipleProfanityWords(): void
  {
    $this->assertSame('This **** is **** annoying', $this->sanitizer->sanitizeWithLocale('This shit is damn annoying'));
  }

  public function testProfanityReplacementMatchesLength(): void
  {
    $this->assertSame('What an ***', $this->sanitizer->sanitizeWithLocale('What an ass'));
  }

  // --- Leetspeak detection ---

  public function testDetectsLeetspeak(): void
  {
    $this->assertSame('What the ****', $this->sanitizer->sanitizeWithLocale('What the f4ck'));
  }

  public function testDetectsLeetspeakDollarSign(): void
  {
    $this->assertSame('You are an *******', $this->sanitizer->sanitizeWithLocale('You are an a$$hole'));
  }

  public function testDetectsLeetspeakComplex(): void
  {
    $this->assertSame('What a **** show', $this->sanitizer->sanitizeWithLocale('What a $h1t show'));
  }

  // --- Homoglyph detection ---

  public function testDetectsCyrillicHomoglyphs(): void
  {
    // "ass" with Cyrillic а (U+0430) instead of Latin a
    $this->assertSame('***', $this->sanitizer->sanitizeWithLocale("\u{0430}ss"));
  }

  // --- Spaced characters ---

  public function testDetectsSpacedCharacters(): void
  {
    $this->assertSame('******* this', $this->sanitizer->sanitizeWithLocale('f u c k this'));
  }

  public function testDetectsSpacedCharactersMultipleSpaces(): void
  {
    $this->assertSame('********** this', $this->sanitizer->sanitizeWithLocale('f  u  c  k this'));
  }

  // --- Multi-language ---

  public function testFiltersGermanProfanityWithDeLocale(): void
  {
    $this->assertSame('Du bist ein *********', $this->sanitizer->sanitizeWithLocale('Du bist ein Arschloch', 'de_DE'));
  }

  public function testFiltersEnglishProfanityWithNonEnglishLocale(): void
  {
    $this->assertSame('What the ****', $this->sanitizer->sanitizeWithLocale('What the fuck', 'de_DE'));
  }

  public function testMissingLocaleWordListStillFiltersEnglish(): void
  {
    $this->assertSame('What the ****', $this->sanitizer->sanitizeWithLocale('What the fuck', 'af_ZA'));
  }

  // --- Combined scenarios ---

  public function testCombinedEmailAndProfanity(): void
  {
    $this->assertSame('Email me at [contact removed] you ****', $this->sanitizer->sanitizeWithLocale('Email me at bad@evil.com you shit'));
  }

  public function testCombinedInvisibleCharsAndProfanity(): void
  {
    $this->assertSame('****', $this->sanitizer->sanitizeWithLocale("fu\u{200B}ck"));
  }

  public function testCombinedContactLinkAndEmail(): void
  {
    $this->assertSame(
      'Join [contact removed] or email [contact removed]',
      $this->sanitizer->sanitizeWithLocale('Join https://discord.gg/abc or email me@test.com')
    );
  }

  public function testFullCombination(): void
  {
    $input = "Join\u{200B} https://t.me/mygroup or email bad@evil.com you shit";
    $expected = 'Join [contact removed] or email [contact removed] you ****';
    $this->assertSame($expected, $this->sanitizer->sanitizeWithLocale($input));
  }

  // --- Locale resolution from request ---

  public function testSanitizeUsesRequestLocale(): void
  {
    $request = new Request();
    $request->setLocale('de_DE');
    $requestStack = new RequestStack();
    $requestStack->push($request);

    $sanitizer = new TextSanitizer($requestStack, $this->wordListDir);
    $this->assertSame('Du bist ein *********', $sanitizer->sanitize('Du bist ein Arschloch'));
  }

  public function testSanitizeFallsBackToEnglishWithoutRequest(): void
  {
    $requestStack = $this->createStub(RequestStack::class);
    $requestStack->method('getCurrentRequest')->willReturn(null);

    $sanitizer = new TextSanitizer($requestStack, $this->wordListDir);
    $this->assertSame('What the ****', $sanitizer->sanitize('What the fuck'));
  }

  public function testSanitizePassesThroughWhenDisabled(): void
  {
    $requestStack = $this->createStub(RequestStack::class);
    $sanitizer = new TextSanitizer($requestStack, $this->wordListDir, enabled: false);
    $this->assertSame('What the fuck', $sanitizer->sanitizeWithLocale('What the fuck'));
  }
}
