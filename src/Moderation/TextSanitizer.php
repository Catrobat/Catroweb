<?php

declare(strict_types=1);

namespace App\Moderation;

final class TextSanitizer
{
  private const string CONTACT_PLACEHOLDER = '[contact removed]';

  /**
   * Keep this list intentionally small and high-confidence to reduce false positives
   * across the platform's many supported languages.
   */
  private const array HIGH_CONFIDENCE_PATTERNS = [
    '/(?<![\p{L}\p{N}])motherfucker(?:s)?(?![\p{L}\p{N}])/iu',
    '/(?<![\p{L}\p{N}])fuck(?:er|ers|ing)?(?![\p{L}\p{N}])/iu',
    '/(?<![\p{L}\p{N}])cunt(?:s)?(?![\p{L}\p{N}])/iu',
    '/(?<![\p{L}\p{N}])nigg(?:er|ers|a|as)(?![\p{L}\p{N}])/iu',
    '/(?<![\p{L}\p{N}])faggot(?:s)?(?![\p{L}\p{N}])/iu',
    '/(?<![\p{L}\p{N}])kike(?:s)?(?![\p{L}\p{N}])/iu',
    '/(?<![\p{L}\p{N}])trann(?:y|ies)(?![\p{L}\p{N}])/iu',
  ];

  private const array CONTACT_PATTERNS = [
    '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/iu',
    '~\b(?:https?://)?(?:www\.)?(?:discord(?:app)?\.com/invite|discord\.gg|t\.me|telegram\.me|wa\.me|chat\.whatsapp\.com|snapchat\.com/add)\S*~iu',
  ];

  public static function sanitize(?string $text): ?string
  {
    if (null === $text || '' === $text) {
      return $text;
    }

    $sanitized = preg_replace('/[\x{00AD}\x{200B}\x{2060}\x{FEFF}]/u', '', $text) ?? $text;

    foreach (self::CONTACT_PATTERNS as $pattern) {
      $sanitized = preg_replace($pattern, self::CONTACT_PLACEHOLDER, $sanitized) ?? $sanitized;
    }

    foreach (self::HIGH_CONFIDENCE_PATTERNS as $pattern) {
      $sanitized = preg_replace_callback(
        $pattern,
        static fn (array $match): string => str_repeat('*', max(3, mb_strlen($match[0]))),
        $sanitized
      ) ?? $sanitized;
    }

    return $sanitized;
  }
}
