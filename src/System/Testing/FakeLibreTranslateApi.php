<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Translation\LibreTranslateApi;
use App\Translation\TranslationResult;
use App\Translation\UnsupportedLanguageException;

class FakeLibreTranslateApi extends LibreTranslateApi
{
  #[\Override]
  public function translate(string $text, ?string $source_language, string $target_language, int $timeout = 5): ?TranslationResult
  {
    if ('untranslatable' === $text) {
      return null;
    }

    // Stands in for a language LibreTranslate has no model for.
    if ('ru' === $source_language) {
      throw new UnsupportedLanguageException('ru is not supported');
    }

    $result = new TranslationResult();
    $result->translation = 'translated '.$text;
    if (null === $source_language) {
      $result->detected_source_language = 'en';
    }

    return $result;
  }

  #[\Override]
  public function getSupportedLanguages(): array
  {
    return ['de', 'en', 'fr', 'ru', 'zh-CN'];
  }
}
