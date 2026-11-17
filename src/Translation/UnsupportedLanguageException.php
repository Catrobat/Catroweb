<?php

declare(strict_types=1);

namespace App\Translation;

/**
 * LibreTranslate has no model for the source or target language: retrying won't help.
 */
class UnsupportedLanguageException extends \RuntimeException
{
}
