<?php

declare(strict_types=1);

namespace App\Translation;

interface TranslationApiInterface
{
  /**
   * $target_language and $source_language must be valid language codes, formatted as follows:
   * Either lowercase 2-character ISO-639-1 language code
   * or 5-character BCP 47 locale code formed by
   *     - lowercase 2-character ISO-639-1 language code
   *     - a hyphen
   *     - uppercase 2-character ISO-3166-1 country code.
   *
   * The concrete implementations do NOT check the validity of the language code formats.
   * The underlying API might return errors if supplied invalid language code.
   *
   * @param string      $text            to be translated
   * @param string|null $source_language null if api is auto detecting language
   *
   * @return TranslationResult|null null if any error occurred
   */
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult;

  /**
   * Translation API may have preference on certain types of translation tasks, depending on language
   * and text length, etc.
   *
   * The parameters are the same as @see TranslationApiInterface::translate
   *
   * @return float between 0 and 1, with 0 meaning the API will not be able to handle this translation
   *               task, and 1 meaning the API is best at handling this task
   */
  public function getPreference(string $text, ?string $source_language, string $target_language): float;
}
