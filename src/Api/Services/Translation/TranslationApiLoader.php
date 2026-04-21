<?php

declare(strict_types=1);

namespace App\Api\Services\Translation;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;

class TranslationApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly TranslationDelegate $translation_delegate,
    private readonly ProjectCustomTranslationRepository $custom_translation_repository,
  ) {
  }

  /**
   * @return array<TranslationResult|null>|null
   */
  public function translateProject(Project $project, ?string $source_language, string $target_language): ?array
  {
    return $this->translation_delegate->translateProject($project, $source_language, $target_language);
  }

  public function getCustomTranslation(Project $project, string $field, string $language): ?string
  {
    return match ($field) {
      'name' => $this->translation_delegate->getProjectNameCustomTranslation($project, $language),
      'description' => $this->translation_delegate->getProjectDescriptionCustomTranslation($project, $language),
      'credit' => $this->translation_delegate->getProjectCreditCustomTranslation($project, $language),
      default => null,
    };
  }

  /**
   * @return string[]
   */
  public function listDefinedLanguages(Project $project): array
  {
    return $this->custom_translation_repository->listDefinedLanguages($project);
  }
}
