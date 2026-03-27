<?php

declare(strict_types=1);

namespace App\Moderation;

use ConsoleTVs\Profanity\Builder;
use ConsoleTVs\Profanity\Classes\Blocker;

final class TextSanitizer
{
  private const string CONTACT_PLACEHOLDER = '[contact removed]';

  private const array CONTACT_PATTERNS = [
    '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/iu',
    '~\b(?:https?://)?(?:www\.)?(?:discord(?:app)?\.com/invite|discord\.gg|t\.me|telegram\.me|wa\.me|chat\.whatsapp\.com|snapchat\.com/add)\S*~iu',
  ];

  private static ?Blocker $profanity_blocker = null;

  public static function sanitize(?string $text): ?string
  {
    if (null === $text || '' === $text) {
      return $text;
    }

    $sanitized = preg_replace('/[\x{00AD}\x{200B}\x{2060}\x{FEFF}]/u', '', $text) ?? $text;

    foreach (self::CONTACT_PATTERNS as $pattern) {
      $sanitized = preg_replace($pattern, self::CONTACT_PLACEHOLDER, $sanitized) ?? $sanitized;
    }

    return self::getProfanityBlocker()
      ->text($sanitized)
      ->filter();
  }

  private static function getProfanityBlocker(): Blocker
  {
    return self::$profanity_blocker ??= Builder::blocker('', '*')
      ->strict(false)
      ->strictClean(true);
  }
}
