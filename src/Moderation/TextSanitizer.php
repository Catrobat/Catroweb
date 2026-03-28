<?php

declare(strict_types=1);

namespace App\Moderation;

/**
 * Sanitizes user-generated content by stripping invisible characters,
 * redacting contact information, and filtering profanity.
 *
 * Uses static methods so entities (which lack DI) can call it directly.
 * The word list is loaded lazily on first use.
 */
class TextSanitizer
{
  /** @var string[]|null */
  private static ?array $profanity_words = null;

  private static ?string $word_list_path = null;

  /**
   * Override the default word list path. Useful for testing.
   */
  public static function setWordListPath(?string $path): void
  {
    self::$word_list_path = $path;
    self::$profanity_words = null; // force reload
  }

  /**
   * Sanitize a text string: strip invisible chars, redact contacts, filter profanity.
   */
  public static function sanitize(?string $text): ?string
  {
    if (null === $text || '' === $text) {
      return $text;
    }

    $text = self::stripInvisibleCharacters($text);
    $text = self::redactEmails($text);
    $text = self::redactContactLinks($text);

    return self::filterProfanity($text);
  }

  /**
   * Remove invisible Unicode obfuscation characters that can be used to bypass filters.
   */
  public static function stripInvisibleCharacters(string $text): string
  {
    // Zero-width space (U+200B), zero-width non-joiner (U+200C),
    // zero-width joiner (U+200D), word joiner (U+2060),
    // soft hyphen (U+00AD), BOM / zero-width no-break space (U+FEFF)
    return preg_replace('/[\x{200B}\x{200C}\x{200D}\x{2060}\x{00AD}\x{FEFF}]/u', '', $text) ?? $text;
  }

  /**
   * Redact email addresses to prevent off-platform contact sharing.
   */
  public static function redactEmails(string $text): string
  {
    return preg_replace(
      '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
      '[contact removed]',
      $text
    ) ?? $text;
  }

  /**
   * Redact off-platform invite/contact links (Discord, Telegram, WhatsApp, Snapchat).
   */
  public static function redactContactLinks(string $text): string
  {
    $patterns = [
      // Discord invite links
      '/https?:\/\/(?:www\.)?discord(?:\.gg|\.com\/invite)\/[a-zA-Z0-9\-]+/i',
      // Telegram links
      '/https?:\/\/(?:www\.)?t\.me\/[a-zA-Z0-9_]+/i',
      // WhatsApp group/chat links
      '/https?:\/\/(?:www\.)?(?:chat\.whatsapp\.com|wa\.me)\/[a-zA-Z0-9\-]+/i',
      // Snapchat profile links
      '/https?:\/\/(?:www\.)?snapchat\.com\/add\/[a-zA-Z0-9._\-]+/i',
    ];

    return preg_replace($patterns, '[contact removed]', $text) ?? $text;
  }

  /**
   * Replace profanity words with asterisks, matching whole words only (case insensitive).
   */
  public static function filterProfanity(string $text): string
  {
    $words = self::loadWordList();
    if ([] === $words) {
      return $text;
    }

    // Build a single regex with all words joined by pipe, using word boundaries
    $escaped = array_map(static fn (string $word): string => preg_quote($word, '/'), $words);
    $pattern = '/\b('.implode('|', $escaped).')\b/iu';

    return preg_replace_callback(
      $pattern,
      static fn (array $matches): string => str_repeat('*', mb_strlen($matches[0])),
      $text
    ) ?? $text;
  }

  /**
   * @return string[]
   */
  private static function loadWordList(): array
  {
    if (null !== self::$profanity_words) {
      return self::$profanity_words;
    }

    $path = self::$word_list_path ?? self::getDefaultWordListPath();

    if (!is_file($path)) {
      self::$profanity_words = [];

      return [];
    }

    $content = file_get_contents($path);
    if (false === $content) {
      self::$profanity_words = [];

      return [];
    }

    $lines = explode("\n", $content);
    self::$profanity_words = array_values(array_filter(
      array_map('trim', $lines),
      static fn (string $line): bool => '' !== $line && !str_starts_with($line, '#')
    ));

    return self::$profanity_words;
  }

  private static function getDefaultWordListPath(): string
  {
    // Walk up from src/Moderation/ to project root
    return dirname(__DIR__, 2).'/config/moderation/profanity_words.txt';
  }
}
