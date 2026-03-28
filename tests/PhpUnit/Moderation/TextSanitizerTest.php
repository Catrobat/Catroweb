<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\Moderation\TextSanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TextSanitizer::class)]
class TextSanitizerTest extends TestCase
{
  protected function setUp(): void
  {
    // Use the real word list from the project
    TextSanitizer::setWordListPath(dirname(__DIR__, 3).'/config/moderation/profanity_words.txt');
  }

  protected function tearDown(): void
  {
    TextSanitizer::setWordListPath(null);
  }

  // --- Null / Empty handling ---

  public function testSanitizeReturnsNullForNull(): void
  {
    $this->assertNull(TextSanitizer::sanitize(null));
  }

  public function testSanitizeReturnsEmptyStringForEmpty(): void
  {
    $this->assertSame('', TextSanitizer::sanitize(''));
  }

  public function testSanitizePreservesNormalText(): void
  {
    $this->assertSame('Hello, world!', TextSanitizer::sanitize('Hello, world!'));
  }

  // --- Invisible character removal ---

  public function testStripsZeroWidthSpace(): void
  {
    $this->assertSame('hello', TextSanitizer::sanitize("hel\u{200B}lo"));
  }

  public function testStripsZeroWidthNonJoiner(): void
  {
    $this->assertSame('hello', TextSanitizer::sanitize("hel\u{200C}lo"));
  }

  public function testStripsZeroWidthJoiner(): void
  {
    $this->assertSame('hello', TextSanitizer::sanitize("hel\u{200D}lo"));
  }

  public function testStripsSoftHyphen(): void
  {
    $this->assertSame('hello', TextSanitizer::sanitize("hel\u{00AD}lo"));
  }

  public function testStripsWordJoiner(): void
  {
    $this->assertSame('hello', TextSanitizer::sanitize("hel\u{2060}lo"));
  }

  public function testStripsBom(): void
  {
    $this->assertSame('hello', TextSanitizer::sanitize("\u{FEFF}hello"));
  }

  public function testStripsMultipleInvisibleCharacters(): void
  {
    $input = "\u{FEFF}h\u{200B}e\u{200C}l\u{200D}l\u{00AD}o\u{2060}";
    $this->assertSame('hello', TextSanitizer::sanitize($input));
  }

  // --- Email redaction ---

  public function testRedactsSimpleEmail(): void
  {
    $this->assertSame(
      'Contact me at [contact removed] for info',
      TextSanitizer::sanitize('Contact me at user@example.com for info')
    );
  }

  public function testRedactsEmailWithSubdomain(): void
  {
    $this->assertSame(
      'Send to [contact removed]',
      TextSanitizer::sanitize('Send to user@mail.example.co.uk')
    );
  }

  public function testRedactsMultipleEmails(): void
  {
    $this->assertSame(
      '[contact removed] and [contact removed]',
      TextSanitizer::sanitize('a@b.com and c@d.org')
    );
  }

  // --- Contact link redaction ---

  public function testRedactsDiscordInviteGg(): void
  {
    $this->assertSame(
      'Join [contact removed] now',
      TextSanitizer::sanitize('Join https://discord.gg/abc123 now')
    );
  }

  public function testRedactsDiscordInviteComInvite(): void
  {
    $this->assertSame(
      'Join [contact removed] now',
      TextSanitizer::sanitize('Join https://discord.com/invite/abc123 now')
    );
  }

  public function testRedactsTelegramLink(): void
  {
    $this->assertSame(
      'Chat at [contact removed] please',
      TextSanitizer::sanitize('Chat at https://t.me/mygroup please')
    );
  }

  public function testRedactsWhatsAppChatLink(): void
  {
    $this->assertSame(
      '[contact removed]',
      TextSanitizer::sanitize('https://chat.whatsapp.com/invite123')
    );
  }

  public function testRedactsWhatsAppWaMe(): void
  {
    $this->assertSame(
      '[contact removed]',
      TextSanitizer::sanitize('https://wa.me/1234567890')
    );
  }

  public function testRedactsSnapchatLink(): void
  {
    $this->assertSame(
      'Add me [contact removed]',
      TextSanitizer::sanitize('Add me https://snapchat.com/add/myuser')
    );
  }

  // --- Profanity filtering ---

  public function testFiltersProfanityWord(): void
  {
    $result = TextSanitizer::sanitize('What the fuck is this');
    $this->assertSame('What the **** is this', $result);
  }

  public function testFiltersProfanityCaseInsensitive(): void
  {
    $result = TextSanitizer::sanitize('What the FUCK is this');
    $this->assertSame('What the **** is this', $result);
  }

  public function testFiltersProfanityMixedCase(): void
  {
    $result = TextSanitizer::sanitize('What the FuCk is this');
    $this->assertSame('What the **** is this', $result);
  }

  public function testDoesNotFilterPartialWordMatches(): void
  {
    // "ass" is in the word list, but "classic" should NOT be filtered
    $this->assertSame('This is a classic game', TextSanitizer::sanitize('This is a classic game'));
  }

  public function testDoesNotFilterPartialWordAssassinate(): void
  {
    $this->assertSame('assassinate the target', TextSanitizer::sanitize('assassinate the target'));
  }

  public function testDoesNotFilterPartialWordGrass(): void
  {
    $this->assertSame('The grass is green', TextSanitizer::sanitize('The grass is green'));
  }

  public function testDoesNotFilterPartialWordScunthorpe(): void
  {
    // "cunt" is in the word list, but "Scunthorpe" should NOT be filtered
    $this->assertSame('I live in Scunthorpe', TextSanitizer::sanitize('I live in Scunthorpe'));
  }

  public function testFiltersMultipleProfanityWords(): void
  {
    $result = TextSanitizer::sanitize('This shit is damn annoying');
    $this->assertSame('This **** is **** annoying', $result);
  }

  public function testProfanityReplacementMatchesWordLength(): void
  {
    $result = TextSanitizer::sanitize('What an ass');
    $this->assertSame('What an ***', $result);
  }

  // --- Combined scenarios ---

  public function testCombinedEmailAndProfanity(): void
  {
    $result = TextSanitizer::sanitize('Email me at bad@evil.com you shit');
    $this->assertSame('Email me at [contact removed] you ****', $result);
  }

  public function testCombinedInvisibleCharsAndProfanity(): void
  {
    // Invisible chars are stripped first, then profanity filtered
    $result = TextSanitizer::sanitize("fu\u{200B}ck");
    $this->assertSame('****', $result);
  }

  public function testCombinedContactLinkAndEmail(): void
  {
    $result = TextSanitizer::sanitize('Join https://discord.gg/abc or email me@test.com');
    $this->assertSame('Join [contact removed] or email [contact removed]', $result);
  }

  public function testFullCombination(): void
  {
    $input = "Join\u{200B} https://t.me/mygroup or email bad@evil.com you shit";
    $expected = 'Join [contact removed] or email [contact removed] you ****';
    $this->assertSame($expected, TextSanitizer::sanitize($input));
  }

  // --- Word list handling ---

  public function testEmptyWordListSkipsProfanityFilter(): void
  {
    // Create a temporary empty word list
    $tmpFile = tempnam(sys_get_temp_dir(), 'profanity_test_');
    file_put_contents($tmpFile, '');
    TextSanitizer::setWordListPath($tmpFile);

    $this->assertSame('What the fuck', TextSanitizer::sanitize('What the fuck'));

    unlink($tmpFile);
  }

  public function testMissingWordListSkipsProfanityFilter(): void
  {
    TextSanitizer::setWordListPath('/nonexistent/path/words.txt');

    $this->assertSame('What the fuck', TextSanitizer::sanitize('What the fuck'));
  }

  public function testWordListIgnoresCommentLines(): void
  {
    $tmpFile = tempnam(sys_get_temp_dir(), 'profanity_test_');
    file_put_contents($tmpFile, "# this is a comment\nbadword\n");
    TextSanitizer::setWordListPath($tmpFile);

    $this->assertSame('a ******* day', TextSanitizer::sanitize('a badword day'));
    $this->assertSame('this is a comment', TextSanitizer::sanitize('this is a comment'));

    unlink($tmpFile);
  }

  public function testWordListIgnoresBlankLines(): void
  {
    $tmpFile = tempnam(sys_get_temp_dir(), 'profanity_test_');
    file_put_contents($tmpFile, "\n\nbadword\n\n");
    TextSanitizer::setWordListPath($tmpFile);

    $this->assertSame('a ******* day', TextSanitizer::sanitize('a badword day'));

    unlink($tmpFile);
  }
}
