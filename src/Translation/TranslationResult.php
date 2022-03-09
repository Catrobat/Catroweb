<?php

namespace App\Translation;

class TranslationResult
{
  public string $translation;
  public ?string $detected_source_language = null;
  public string $provider;
  public ?string $cache = null;
}
