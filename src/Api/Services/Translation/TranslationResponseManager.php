<?php

declare(strict_types=1);

namespace App\Api\Services\Translation;

use App\Api\Services\Base\AbstractResponseManager;
use App\Translation\TranslationResult;
use OpenAPI\Server\Model\ProjectCustomTranslationResponse;
use OpenAPI\Server\Model\ProjectTranslationResponse;

class TranslationResponseManager extends AbstractResponseManager
{
  /**
   * @param array<TranslationResult|null> $translation_result
   */
  public function createProjectTranslationResponse(
    string $project_id,
    ?string $source_language,
    string $target_language,
    array $translation_result,
  ): ProjectTranslationResponse {
    $title_translation = $translation_result[0] ?? null;

    return new ProjectTranslationResponse([
      'id' => $project_id,
      'source_language' => $source_language ?? $title_translation?->detected_source_language,
      'target_language' => $target_language,
      'translated_title' => $title_translation?->translation,
      'translated_description' => ($translation_result[1] ?? null)?->translation,
      'translated_credit' => ($translation_result[2] ?? null)?->translation,
      'provider' => $title_translation?->provider,
      '_cache' => $title_translation?->cache,
    ]);
  }

  public function createCustomTranslationResponse(?string $translation): ProjectCustomTranslationResponse
  {
    return new ProjectCustomTranslationResponse([
      'translation' => $translation,
    ]);
  }
}
