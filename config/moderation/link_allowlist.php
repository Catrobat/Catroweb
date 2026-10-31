<?php

declare(strict_types=1);

/**
 * Allowlist of trusted link hosts for user-generated text (comments, project descriptions, studio texts).
 *
 * Rules:
 *   - An entry matches a host exactly OR as a suffix preceded by a dot.
 *     e.g. 'wikipedia.org' matches 'wikipedia.org' and 'en.wikipedia.org' but not 'fakewikipedia.org'.
 *   - Matching is case-insensitive.
 *   - The userinfo trick (https://wikipedia.org@evil.com) resolves to 'evil.com' – not matched.
 *
 * When a link in user content has a host NOT in this list it is replaced with [link removed].
 * Contact-platform links (Discord, Telegram, …) are removed by TextSanitizer before this check.
 *
 * To add a new domain: append it to the array below.
 *
 * @see https://github.com/Catrobat/Catroweb/issues/7388
 *
 * @return string[]
 */
return [
    // Catrobat / project ecosystem
    'catrobat.org',
    'catrob.at',
    'share.catrobat.org',
    'pocketcode.org',

    // Educational / institutional TLDs (suffix match covers all *.edu and *.ac.*)
    'edu',          // matches any *.edu host
    'ac.at',
    'ac.uk',
    'ac.jp',
    'ac.de',
    'uni-graz.at',
    'tugraz.at',
    'edu.au',

    // Trusted educational / reference sites
    'wikipedia.org',
    'wikimedia.org',
    'wikidata.org',
    'scratch.mit.edu',
    'mit.edu',
    'khan academy.org',
    'khanacademy.org',
    'stackoverflow.com',
    'stackexchange.com',

    // Code hosting (safe for open-source links)
    'github.com',
    'gitlab.com',
    'codeberg.org',

    // General safe references
    'youtube.com',
    'youtu.be',
    'vimeo.com',
    'archive.org',
    'creativecommons.org',
];
