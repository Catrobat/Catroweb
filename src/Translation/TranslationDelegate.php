<?php

declare(strict_types=1);

namespace App\Translation;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Messenger\MessageBusInterface;

class TranslationDelegate
{
  private const string JOB_QUEUED = 'queued';
  private const string JOB_FAILED = 'failed';
  // A queued job older than this is presumed lost and queued again; a failure is reported for this long.
  private const int JOB_TTL = 180;
  // Per LibreTranslate call: a 1,300-character description took 42 s on the production container.
  private const int JOB_CALL_TIMEOUT = 120;

  public function __construct(
    private readonly ProjectCustomTranslationRepository $project_custom_translation_repository,
    private readonly ProjectMachineTranslationRepository $project_machine_translation_repository,
    private readonly LibreTranslateApi $api,
    private readonly CacheItemPoolInterface $cache,
    private readonly MessageBusInterface $message_bus,
  ) {
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function addProjectNameCustomTranslation(Project $project, string $target_language, string $name_translation): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->addNameTranslation($project, $target_language, $name_translation);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function getProjectNameCustomTranslation(Project $project, string $target_language): ?string
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->getNameTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function deleteProjectNameCustomTranslation(Project $project, string $target_language): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->deleteNameTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function addProjectDescriptionCustomTranslation(Project $project, string $target_language, string $description_translation): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->addDescriptionTranslation($project, $target_language, $description_translation);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function getProjectDescriptionCustomTranslation(Project $project, string $target_language): ?string
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->getDescriptionTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function deleteProjectDescriptionCustomTranslation(Project $project, string $target_language): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->deleteDescriptionTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function addProjectCreditCustomTranslation(Project $project, string $target_language, string $credit_translation): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->addCreditTranslation($project, $target_language, $credit_translation);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function getProjectCreditCustomTranslation(Project $project, string $target_language): ?string
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->getCreditTranslation($project, $target_language);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function deleteProjectCreditCustomTranslation(Project $project, string $target_language): bool
  {
    $this->validateLanguage($target_language);

    return $this->project_custom_translation_repository->deleteCreditTranslation($project, $target_language);
  }

  /**
   * The cached translation of the project's current texts, or null when there is none yet.
   *
   * @return array<TranslationResult|null>|null
   *
   * @throws \InvalidArgumentException
   */
  public function getCachedProjectTranslation(Project $project, ?string $source_language, string $target_language): ?array
  {
    [$source_language, $target_language] = $this->machineLanguages($source_language, $target_language);

    return $this->project_machine_translation_repository->getCachedTranslation($project, $source_language, $target_language);
  }

  /**
   * Queues the translation unless it is queued already. False when the last attempt failed: the caller reports it,
   * and the next request queues a new attempt.
   *
   * @throws \InvalidArgumentException
   */
  public function requestProjectTranslation(Project $project, ?string $source_language, string $target_language): bool
  {
    [$source_language, $target_language] = $this->machineLanguages($source_language, $target_language);
    $state = $this->cache->getItem($this->jobKey($project, $source_language, $target_language));
    if (self::JOB_FAILED === $state->get()) {
      $this->cache->deleteItem($state->getKey());

      return false;
    }

    if (self::JOB_QUEUED !== $state->get()) {
      $this->cache->save($state->set(self::JOB_QUEUED)->expiresAfter(self::JOB_TTL));
      $this->message_bus->dispatch(new TranslateProject((string) $project->getId(), $source_language, $target_language));
      // Tests run the job right here (sync transport).
      if (self::JOB_FAILED === $this->cache->getItem($state->getKey())->get()) {
        $this->cache->deleteItem($state->getKey());

        return false;
      }
    }

    return true;
  }

  /**
   * Translates and caches the project's texts (TranslateProject job). Takes about 1 s per 30 characters.
   */
  public function translateProject(Project $project, ?string $source_language, string $target_language): void
  {
    $key = $this->jobKey($project, $source_language, $target_language);
    // Already done by an earlier job for the same languages.
    if (null !== $this->project_machine_translation_repository->getCachedTranslation($project, $source_language, $target_language)) {
      $this->cache->deleteItem($key);

      return;
    }

    $texts = ['name' => $project->getName(), 'description' => $project->getDescription(), 'credits' => $project->getCredits()];
    $translated = [];
    $detected = null;
    foreach ($texts as $field => $text) {
      // Uploads store missing descriptions and credits as ''; there is nothing to translate or show.
      if (null === $text || '' === trim($text)) {
        continue;
      }

      $result = $this->api->translate($text, $source_language, $target_language, self::JOB_CALL_TIMEOUT);
      if (!$result instanceof TranslationResult) {
        $this->cache->save($this->cache->getItem($key)->set(self::JOB_FAILED)->expiresAfter(self::JOB_TTL));

        return;
      }

      $translated[$field] = $result->translation;
      if ('name' === $field) {
        $detected = $result->detected_source_language;
      }
    }

    $source = $source_language ?? $detected;
    $name = $translated['name'] ?? null;
    if (null === $source || null === $name) {
      $this->cache->save($this->cache->getItem($key)->set(self::JOB_FAILED)->expiresAfter(self::JOB_TTL));

      return;
    }

    $this->project_machine_translation_repository->cacheTranslation($project, $source, $target_language, null === $source_language, $name, $translated['description'] ?? null, $translated['credits'] ?? null);
    $this->cache->deleteItem($key);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    [$source_language, $target_language] = $this->machineLanguages($source_language, $target_language);

    return $this->api->translate($text, $source_language, $target_language);
  }

  /**
   * @return array{0: string|null, 1: string}
   *
   * @throws \InvalidArgumentException
   */
  private function machineLanguages(?string $source_language, string $target_language): array
  {
    $source_language = $this->machineLanguage($source_language);
    $target_language = $this->machineLanguage($target_language);
    $this->validateLanguage($source_language);
    $this->validateLanguage($target_language);

    return [$source_language, $target_language];
  }

  private function jobKey(Project $project, ?string $source_language, string $target_language): string
  {
    return 'project_translation.'.$project->getId().'.'.($source_language ?? 'auto').'.'.$target_language;
  }

  /**
   * Site locales like sr_SP or ha_HG carry no real country; machine translation only needs the language.
   *
   * @return ($language is null ? null : string)
   */
  private function machineLanguage(?string $language): ?string
  {
    if (null !== $language && 1 === preg_match('/^([a-z]{2})-([A-Z]{2})$/', $language, $m) && !Countries::exists($m[2])) {
      return $m[1];
    }

    return $language;
  }

  private function validateLanguage(?string $language): void
  {
    if (2 === strlen((string) $language)) {
      if (strtolower((string) $language) != $language) {
        throw new \InvalidArgumentException('2-character language code has to be lower case');
      }

      if (!Languages::exists($language)) {
        throw new \InvalidArgumentException('2-character language code is invalid');
      }
    } elseif (5 === strlen((string) $language)) {
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
}
