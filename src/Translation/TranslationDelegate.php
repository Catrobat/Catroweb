<?php

declare(strict_types=1);

namespace App\Translation;

use App\DB\Entity\Project\Program;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class TranslationDelegate
{
  private readonly array $apis;

  public function __construct(private readonly ProjectCustomTranslationRepository $project_custom_translation_repository,
    private readonly ProjectMachineTranslationRepository $project_machine_translation_repository,
    TranslationApiInterface ...$apis)
  {
    $this->apis = $apis;
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function addProjectNameCustomTranslation(Program $project, string $target_language, string $name_translation): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->addNameTranslation($project, $target_language, $name_translation);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function getProjectNameCustomTranslation(Program $project, string $target_language): ?string
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->getNameTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function deleteProjectNameCustomTranslation(Program $project, string $target_language): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->deleteNameTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function addProjectDescriptionCustomTranslation(Program $project, string $target_language, string $description_translation): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->addDescriptionTranslation($project, $target_language, $description_translation);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function getProjectDescriptionCustomTranslation(Program $project, string $target_language): ?string
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->getDescriptionTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function deleteProjectDescriptionCustomTranslation(Program $project, string $target_language): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->deleteDescriptionTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function addProjectCreditCustomTranslation(Program $project, string $target_language, string $credit_translation): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->addCreditTranslation($project, $target_language, $credit_translation);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function getProjectCreditCustomTranslation(Program $project, string $target_language): ?string
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->getCreditTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function deleteProjectCreditCustomTranslation(Program $project, string $target_language): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->deleteCreditTranslation($project, $target_language);
  }

  /**
   * @psalm-return array<array-key, TranslationResult|null>|null
   *
   * @throws \InvalidArgumentException
   */
  public function translateProject(Program $project, ?string $source_language, string $target_language): ?array
  {
    $this->validateLanguage($source_language);
    $this->validateLanguage($target_language);

    $cached_result = $this->getCachedProjectTranslation($project, $source_language, $target_language);
    if (null !== $cached_result) {
      return $cached_result;
    }

    $to_translate = [$project->getName(), $project->getDescription(), $project->getCredits()];
    $translation_result = [];

    foreach ($to_translate as $text) {
      if (null == $text) {
        $translation_result[] = null;
        continue;
      }

      $translated_text = $this->internalTranslate($text, $source_language, $target_language);

      if (!$translated_text instanceof TranslationResult) {
        return null;
      }

      $translation_result[] = $translated_text;
    }

    return $translation_result;
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $this->validateLanguage($source_language);
    $this->validateLanguage($target_language);

    return $this->internalTranslate($text, $source_language, $target_language);
  }

  private function internalTranslate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $apis = $this->orderApi($text, $source_language, $target_language);

    foreach ($apis as $api) {
      $translation = $api->translate($text, $source_language, $target_language);

      if (null != $translation) {
        return $translation;
      }
    }

    return null;
  }

  private function orderApi(string $text, ?string $source_language, string $target_language): array
  {
    $apis = $this->apis;

    usort($apis, static fn (TranslationApiInterface $api_1, TranslationApiInterface $api_2): int => -($api_1->getPreference($text, $source_language, $target_language) <=>
      $api_2->getPreference($text, $source_language, $target_language)));

    return $apis;
  }

  private function validateLanguage(?string $language): void
  {
    if (2 == strlen((string) $language)) {
      if (strtolower((string) $language) != $language) {
        throw new \InvalidArgumentException('2-character language code has to be lower case');
      }

      if (!Languages::exists($language)) {
        throw new \InvalidArgumentException('2-character language code is invalid');
      }
    } elseif (5 == strlen((string) $language)) {
      if ('-' !== $language[2]) {
        throw new \InvalidArgumentException('Invalid 5-character language code format');
      }

      $language_code = substr((string) $language, 0, 2);
      $country_code = substr((string) $language, 3, 2);

      if (strtolower($language_code) !== $language_code) {
        throw new \InvalidArgumentException('5-character language code has to contain lower case language code');
      }

      if (!Languages::exists($language_code)) {
        throw new \InvalidArgumentException('language code in 5-character language code is invalid');
      }

      if (strtoupper($country_code) !== $country_code) {
        throw new \InvalidArgumentException('5-character language code has to contain upper case country code');
      }

      if (!Countries::exists($country_code)) {
        throw new \InvalidArgumentException('country code in 5-character language code is invalid');
      }
    } elseif (null !== $language) {
      throw new \InvalidArgumentException('language has to be null, 2-character or 5-character language code');
    }
  }

  protected function getCachedProjectTranslation(Program $project, ?string $source_language, string $target_language): ?array
  {
    return $this->project_machine_translation_repository->getCachedTranslation($project, $source_language, $target_language);
  }
}
