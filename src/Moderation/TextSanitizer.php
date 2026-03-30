<?php

declare(strict_types=1);

namespace App\Moderation;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class TextSanitizer
{
  private const string REPLACEMENT_CONTACT = '[contact removed]';

  private const string CONTACT_LINKS_PATTERN = '/('
    .'https?:\/\/(?:www\.)?discord(?:app)?(?:\.gg|\.com\/invite)\/[a-zA-Z0-9\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:t\.me|telegram\.me|telegram\.org)\/[a-zA-Z0-9_]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:chat\.whatsapp\.com|wa\.me|api\.whatsapp\.com)\/[a-zA-Z0-9\-+]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:snapchat\.com\/add|t\.snapchat\.com)\/[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:instagram\.com|instagr\.am|ig\.me)\/[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?kik\.me\/[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:tiktok\.com\/@|vm\.tiktok\.com\/|vt\.tiktok\.com\/)[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:facebook\.com|fb\.com|fb\.me|m\.me|messenger\.com)\/[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:signal\.me|signal\.group)\/[a-zA-Z0-9._\-#\/]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:join\.skype\.com\/|skype\.com\/)[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?(?:twitter\.com|x\.com|t\.co)\/[a-zA-Z0-9._\-]+'
    .'|'
    .'https?:\/\/(?:www\.)?twitch\.tv\/[a-zA-Z0-9._\-]+'
    .')/i';

  private const array LEETSPEAK_MAP = [
    '4' => 'a', '@' => 'a', '8' => 'b', '(' => 'c', '3' => 'e',
    '6' => 'g', '#' => 'h', '1' => 'i', '!' => 'i', '|' => 'l',
    '0' => 'o', '5' => 's', '$' => 's', '7' => 't', '+' => 't',
  ];

  private const array HOMOGLYPH_MAP = [
    "\u{0430}" => 'a', "\u{0435}" => 'e', "\u{0456}" => 'i', "\u{043E}" => 'o',
    "\u{0440}" => 'p', "\u{0441}" => 'c', "\u{0443}" => 'y', "\u{0445}" => 'x',
    "\u{0410}" => 'a', "\u{0415}" => 'e', "\u{041E}" => 'o', "\u{0420}" => 'p',
    "\u{0421}" => 'c', "\u{0422}" => 't', "\u{041D}" => 'h', "\u{0412}" => 'b',
    "\u{041A}" => 'k', "\u{041C}" => 'm',
  ];

  /** @var array<string, string[]> */
  private array $wordListCache = [];

  private const int WORDS_PER_PATTERN = 1000;
  private const int SPACED_WORDS_PER_PATTERN = 200;

  /** @var array<string, non-empty-string[]> */
  private array $compiledPatterns = [];

  /** @var array<string, int> */
  private array $minWordLength = [];

  /** @var array<string, non-empty-string[]> */
  private array $spacedPatterns = [];

  public function __construct(
    private readonly RequestStack $requestStack,
    #[Autowire('%kernel.project_dir%/config/moderation/wordlists')]
    private readonly string $wordListDir,
    #[Autowire('%env(bool:TEXT_SANITIZER_ENABLED)%')]
    private readonly bool $enabled = true,
  ) {
  }

  public function sanitize(?string $text): ?string
  {
    $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';

    return $this->sanitizeWithLocale($text, $locale);
  }

  public function sanitizeWithLocale(?string $text, string $locale = 'en'): ?string
  {
    if (!$this->enabled || null === $text || '' === $text) {
      return $text;
    }

    $text = $this->stripInvisibleCharacters($text);
    $text = $this->redactEmails($text);
    $text = $this->redactPhoneNumbers($text);
    $text = $this->redactContactLinks($text);
    $text = $this->redactUriSchemes($text);

    return $this->filterProfanity($text, $locale);
  }

  private function stripInvisibleCharacters(string $text): string
  {
    return preg_replace(
      '/[\x{200B}\x{200C}\x{200D}\x{2060}\x{00AD}\x{FEFF}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u',
      '',
      $text
    ) ?? $text;
  }

  private function redactEmails(string $text): string
  {
    return preg_replace(
      '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
      self::REPLACEMENT_CONTACT,
      $text
    ) ?? $text;
  }

  private function redactPhoneNumbers(string $text): string
  {
    return preg_replace(
      '/(?<!\w)(?:\+|00)[1-9][\d\s.\-()]{6,18}\d(?!\w)/',
      self::REPLACEMENT_CONTACT,
      $text
    ) ?? $text;
  }

  private function redactContactLinks(string $text): string
  {
    return preg_replace(self::CONTACT_LINKS_PATTERN, self::REPLACEMENT_CONTACT, $text) ?? $text;
  }

  private function redactUriSchemes(string $text): string
  {
    return preg_replace(
      '/(?:skype|tg|whatsapp|viber|line|kakaoopen|fb-messenger):\/\/[^\s]+/i',
      self::REPLACEMENT_CONTACT,
      $text
    ) ?? $text;
  }

  private function filterProfanity(string $text, string $locale): string
  {
    $lang = substr($locale, 0, 2);

    $patterns = $this->getCompiledPatterns($lang);
    if ([] === $patterns) {
      return $text;
    }

    $min_len = $this->minWordLength[$this->langKey($lang)] ?? 2;

    // Early exit: text shorter than the shortest profanity word cannot match
    if (mb_strlen($text) < $min_len) {
      return $text;
    }

    // Pass 1: Direct whole-word match on original text
    foreach ($patterns as $pattern) {
      $text = preg_replace_callback(
        $pattern,
        static fn (array $m): string => str_repeat('*', mb_strlen($m[0])),
        $text
      ) ?? $text;
    }

    // Pass 2: Normalized match (leetspeak + homoglyphs)
    $text = $this->filterNormalized($text, $patterns);

    // Pass 3: Spaced character detection (only if text has spaced single chars)
    if (preg_match('/\b\w\s+\w\s+\w\b/u', $text)) {
      $text = $this->filterSpacedCharacters($text, $lang);
    }

    return $text;
  }

  /**
   * @param non-empty-string[] $patterns
   */
  private function filterNormalized(string $text, array $patterns): string
  {
    $normalized = $this->normalizeText($text);
    if ($normalized === mb_strtolower($text)) {
      return $text;
    }

    // Build byte-to-char offset map once for all patterns
    $normalized_chars = mb_str_split($normalized);
    $byte_to_char = [];
    $byte_pos = 0;
    foreach ($normalized_chars as $i => $char) {
      $byte_to_char[$byte_pos] = $i;
      $byte_pos += strlen($char);
    }

    // Collect all matches from all patterns
    $replacements = [];
    foreach ($patterns as $pattern) {
      if (!preg_match_all($pattern, $normalized, $matches, PREG_OFFSET_CAPTURE)) {
        continue;
      }

      foreach ($matches[0] as [$match, $byte_offset]) {
        $char_offset = $byte_to_char[$byte_offset] ?? null;
        if (null === $char_offset) {
          continue;
        }
        $replacements[] = [$char_offset, mb_strlen($match)];
      }
    }

    if ([] === $replacements) {
      return $text;
    }

    // Sort by offset descending so replacements don't shift positions
    usort($replacements, static fn (array $a, array $b): int => $b[0] <=> $a[0]);

    $result = $text;
    foreach ($replacements as [$char_offset, $match_char_len]) {
      $original_segment = mb_substr($result, $char_offset, $match_char_len);
      if (!preg_match('/^\*+$/', $original_segment)) {
        $replacement = str_repeat('*', mb_strlen($original_segment));
        $result = mb_substr($result, 0, $char_offset)
          .$replacement
          .mb_substr($result, $char_offset + $match_char_len);
      }
    }

    return $result;
  }

  private function filterSpacedCharacters(string $text, string $lang): string
  {
    $patterns = $this->getSpacedPatterns($lang);

    foreach ($patterns as $pattern) {
      $text = preg_replace_callback(
        $pattern,
        static fn (array $m): string => str_repeat('*', mb_strlen($m[0])),
        $text
      ) ?? $text;
    }

    return $text;
  }

  private function normalizeText(string $text): string
  {
    $text = mb_strtolower($text);
    $text = strtr($text, self::HOMOGLYPH_MAP);

    return strtr($text, self::LEETSPEAK_MAP);
  }

  private function langKey(string $lang): string
  {
    return 'en' === $lang ? 'en' : "en+{$lang}";
  }

  /**
   * @return string[]
   */
  private function getWordList(string $lang): array
  {
    $key = $this->langKey($lang);

    if (isset($this->wordListCache[$key])) {
      return $this->wordListCache[$key];
    }

    $words = $this->loadWordListFile('en');
    if ('en' !== $lang) {
      $words = array_merge($words, $this->loadWordListFile($lang));
    }

    $words = array_values(array_unique($words));

    // Sort longest first for greedy matching
    usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

    $this->wordListCache[$key] = $words;

    // Cache minimum word length for early exit
    if ([] !== $words) {
      $this->minWordLength[$key] = mb_strlen(end($words));
    }

    return $words;
  }

  /**
   * @return string[]
   */
  private function loadWordListFile(string $lang): array
  {
    $path = $this->wordListDir.'/'.$lang.'.txt';
    if (!is_file($path)) {
      return [];
    }

    $content = file_get_contents($path);
    if (false === $content) {
      return [];
    }

    $lines = explode("\n", $content);

    return array_values(array_filter(
      array_map('trim', $lines),
      static fn (string $line): bool => '' !== $line && !str_starts_with($line, '#')
    ));
  }

  /**
   * @return non-empty-string[]
   */
  private function getCompiledPatterns(string $lang): array
  {
    $key = $this->langKey($lang);

    if (isset($this->compiledPatterns[$key])) {
      return $this->compiledPatterns[$key];
    }

    $words = $this->getWordList($lang);
    if ([] === $words) {
      $this->compiledPatterns[$key] = [];

      return [];
    }

    $patterns = [];
    foreach (array_chunk($words, self::WORDS_PER_PATTERN) as $chunk) {
      $escaped = array_map(static fn (string $w): string => preg_quote($w, '/'), $chunk);
      $patterns[] = '/\b('.implode('|', $escaped).')\b/iu';
    }

    $this->compiledPatterns[$key] = $patterns;

    return $patterns;
  }

  /**
   * @return non-empty-string[]
   */
  private function getSpacedPatterns(string $lang): array
  {
    $key = $this->langKey($lang);

    if (isset($this->spacedPatterns[$key])) {
      return $this->spacedPatterns[$key];
    }

    $words = $this->getWordList($lang);
    $short = array_values(array_filter(
      $words,
      static fn (string $w): bool => mb_strlen($w) >= 3 && mb_strlen($w) <= 8 && !str_contains($w, ' ')
    ));

    if ([] === $short) {
      $this->spacedPatterns[$key] = [];

      return [];
    }

    $patterns = [];
    foreach (array_chunk($short, self::SPACED_WORDS_PER_PATTERN) as $chunk) {
      $alts = [];
      foreach ($chunk as $word) {
        $chars = mb_str_split($word);
        $alts[] = implode('\s+', array_map(
          static fn (string $c): string => preg_quote($c, '/'),
          $chars
        ));
      }
      $patterns[] = '/\b(?:'.implode('|', $alts).')\b/iu';
    }

    $this->spacedPatterns[$key] = $patterns;

    return $patterns;
  }
}
