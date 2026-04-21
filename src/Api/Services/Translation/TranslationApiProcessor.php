<?php

declare(strict_types=1);

namespace App\Api\Services\Translation;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Project\Project;
use App\Translation\TranslationDelegate;

class TranslationApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly TranslationDelegate $translation_delegate)
  {
  }

  public function saveCustomTranslation(Project $project, string $field, string $language, string $text): bool
  {
    return match ($field) {
      'name' => $this->translation_delegate->addProjectNameCustomTranslation($project, $language, $text),
      'description' => $this->translation_delegate->addProjectDescriptionCustomTranslation($project, $language, $text),
      'credit' => $this->translation_delegate->addProjectCreditCustomTranslation($project, $language, $text),
      default => false,
    };
  }

  public function deleteCustomTranslation(Project $project, string $field, string $language): bool
  {
    return match ($field) {
      'name' => $this->translation_delegate->deleteProjectNameCustomTranslation($project, $language),
      'description' => $this->translation_delegate->deleteProjectDescriptionCustomTranslation($project, $language),
      'credit' => $this->translation_delegate->deleteProjectCreditCustomTranslation($project, $language),
      default => false,
    };
  }
}
