<?php

declare(strict_types=1);

namespace App\Moderation;

/**
 * Detects web links in short texts such as usernames, where any link is advertising: the name
 * is shown in follower notifications, comments and project pages.
 *
 * Matches scheme URLs (https://…), "www." prefixes and bare domains ending in a common TLD.
 * Only treats it as a domain when there is no space around the dot, avoiding false positives
 * on sentences ending in a period followed by words that happen to match TLDs (e.g. "it. At").
 */
class LinkDetector
{
  public const string TLDS = 'com|net|org|info|biz|io|co|me|tv|gg|ly|be|to|cc|ws|su|app|dev|xyz|online|site|'
    .'website|shop|store|link|live|top|club|fun|video|stream|social|page|blog|news|one|click|'
    .'de|at|ch|uk|us|fr|it|es|nl|pl|ru|br|in|cn|jp|kr|tk|ml|ga|cf|gq';

  public const string LINK_PATTERN = '~'
    .'[a-z][a-z0-9+.\-]*://[^\s<>"\'\[\]{}|\\\\^`]+'
    .'|\bwww\.[^\s<>"\'\[\]{}|\\\\^`]+'
    .'|(?<![@\p{L}\p{N}])(?:[a-z0-9\-]+\.)+(?:'.self::TLDS.')\b(?::\d+)?(?:/[^\s<>"\'\[\]{}|\\\\^`]*)?'
    .'~iu';

  private const string INVISIBLE_CHARACTERS = '/[\x{200B}\x{200C}\x{200D}\x{2060}\x{00AD}\x{FEFF}]/u';

  public function containsLink(?string $text): bool
  {
    if (null === $text || '' === $text) {
      return false;
    }

    $visible = preg_replace(self::INVISIBLE_CHARACTERS, '', $text) ?? $text;

    return 1 === preg_match(self::LINK_PATTERN, $visible);
  }
}
