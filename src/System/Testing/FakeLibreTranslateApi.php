<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Translation\LibreTranslateApi;
use App\Translation\TranslationResult;

class FakeLibreTranslateApi extends LibreTranslateApi
{
  #[\Override]
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
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
