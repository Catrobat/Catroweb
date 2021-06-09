<?php

namespace App\Translation\TestEnv;

use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use InvalidArgumentException;

class FakeTranslationDelegate extends TranslationDelegate
{
  /**
   * @throws InvalidArgumentException
   */
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $translation_result = new TranslationResult();
    $translation_result->provider = 'itranslate';
    if (null == $source_language) {
      $translation_result->detected_source_language = 'en';
    }
    $translation_result->translation = 'Fixed translation text';

    return $translation_result;
  }
}
