<?php

namespace App\Translation;

use App\Entity\Program;
use InvalidArgumentException;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class TranslationDelegate
{
  private array $apis;

  public function __construct(TranslationApiInterface ...$apis)
  {
    $this->apis = $apis;
  }

  /**
   * @throws InvalidArgumentException
   */
  public function translateProject(Program $project, ?string $source_language, string $target_language): ?array
  {
    $this->validateLanguage($source_language);
    $this->validateLanguage($target_language);

    $to_translate = [$project->getName(), $project->getDescription(), $project->getCredits()];
    $translation_result = [];

    foreach ($to_translate as $text) {
      if (null == $text) {
        array_push($translation_result, null);
        continue;
      }

      $translated_text = $this->internalTranslate($text, $source_language, $target_language);

      if (null === $translated_text) {
        return null;
      }

      array_push($translation_result, $translated_text);
    }

    return $translation_result;
  }

  /**
   * @throws InvalidArgumentException
   */
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $this->validateLanguage($source_language);
    $this->validateLanguage($target_language);

    return $this->internalTranslate($text, $source_language, $target_language);
  }

  private function internalTranslate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    foreach ($this->apis as $api) {
      $translation = $api->translate($text, $source_language, $target_language);

      if (null != $translation) {
        return $translation;
      }
    }

    return null;
  }

  private function validateLanguage(?string $language): void
  {
    if (2 == strlen($language)) {
      if (strtolower($language) != $language) {
        throw new InvalidArgumentException('2-character language code has to be lower case');
      }

      if (!Languages::exists($language)) {
        throw new InvalidArgumentException('2-character language code is invalid');
      }
    } elseif (5 == strlen($language)) {
      if ('-' != $language[2]) {
        throw new InvalidArgumentException('Invalid 5-character language code format');
      }

      $language_code = substr($language, 0, 2);
      $country_code = substr($language, 3, 2);

      if (strtolower($language_code) != $language_code) {
        throw new InvalidArgumentException('5-character language code has to contain lower case language code');
      }

      if (!Languages::exists($language_code)) {
        throw new InvalidArgumentException('language code in 5-character language code is invalid');
      }

      if (strtoupper($country_code) != $country_code) {
        throw new InvalidArgumentException('5-character language code has to contain upper case country code');
      }

      if (!Countries::exists($country_code)) {
        throw new InvalidArgumentException('country code in 5-character language code is invalid');
      }
    } elseif (null !== $language) {
      throw new InvalidArgumentException('language has to be null, 2-character or 5-character language code');
    }
  }
}
