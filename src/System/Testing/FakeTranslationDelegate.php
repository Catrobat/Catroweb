<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\DB\Entity\Project\Program;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;

class FakeTranslationDelegate extends TranslationDelegate
{
  public function translateProject(Program $project, ?string $source_language, string $target_language): ?array
  {
    $cached_result = $this->getCachedProjectTranslation($project, $source_language, $target_language);
    if (null !== $cached_result) {
      return $cached_result;
    }

    $to_translate = [$project->getName(), $project->getDescription(), $project->getCredits()];
    $translation_result = [];

    foreach ($to_translate as $text) {
      if (null == $text) {
        array_push($translation_result, null);
        continue;
      }

      $translated_text = new TranslationResult();
      $translated_text->provider = 'itranslate';
      if (null == $source_language) {
        $translated_text->detected_source_language = 'en';
      }
      $translated_text->translation = 'translated '.$text;

      array_push($translation_result, $translated_text);
    }

    return $translation_result;
  }

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
