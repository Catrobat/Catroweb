<?php

namespace App\Translation;

class TranslationApiHelper
{
  public function __construct(private readonly array $long_language_code)
  {
  }

  public function transformLanguageCode(?string $language): ?string
  {
    if (null === $language) {
      return null;
    }

    if (2 == strlen($language)) {
      return $language;
    }

    if (in_array($language, $this->long_language_code, true)) {
      return $language;
    }

    return substr($language, 0, 2);
  }
}
