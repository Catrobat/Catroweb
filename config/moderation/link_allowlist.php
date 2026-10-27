<?php

declare(strict_types=1);

/**
 * Allowlist of trusted link hosts for user-generated text (comments, project descriptions, studio texts).
 *
 * Rules:
 *   - Exact match on the parsed host (lowercased, trailing dot removed, leading www. stripped).
 *   - No suffix or wildcard subdomain matching: a subdomain only passes if it is on the list itself.
 *   - The userinfo trick (https://wikipedia.org@evil.com) resolves to 'evil.com' – not matched.
 *
 * When the link allowlist is enabled, any link whose host is NOT in this list is replaced with [link removed].
 *
 * @see https://github.com/Catrobat/Catroweb/issues/7388
 *
 * @return string[]
 */
return [
  // Catrobat ecosystem
  'catrobat.org',
  'share.catrobat.org',
  'pocketcode.org',

  // Trusted educational / reference / code hosting sites (exact hosts only)
  'scratch.mit.edu',
  'wikipedia.org',
  'wikimedia.org',
  'wikidata.org',
  'github.com',
  'gitlab.com',
  'codeberg.org',
  'youtube.com',
  'youtu.be',
];
