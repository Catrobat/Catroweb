<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\Project\ProjectManager;
use App\Translation\LibreTranslateApi;
use App\Translation\TranslationBudgetExceededException;
use App\Translation\UnsupportedLanguageException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final readonly class CodeViewTranslation
{
  private const int TEXT_TTL = 604_800;
  private const int JOB_TTL = 180;
  private const int MAX_BATCH_TEXTS = 6;
  private const int MAX_BATCH_CHARACTERS = 900;
  private const int MAX_JOB_TEXTS = 12;
  private const int MAX_JOB_CHARACTERS = 1_500;
  private const int MAX_JOB_SECONDS = 35;
  private const int SAMPLE_CHARACTERS = 1_500;

  public function __construct(
    #[Autowire(service: 'cache.app')]
    private CacheItemPoolInterface $cache,
    private LockFactory $locks,
    private MessageBusInterface $bus,
    private ProjectManager $projects,
    private CodeViewExportStore $store,
    private LibreTranslateApi $provider,
    private RateLimiterFactoryInterface $codeTranslationGlobalLimiter,
    private LoggerInterface $logger,
  ) {
  }

  /**
   * @param \Closure(): bool $may_queue
   *
   * @return array<string, mixed>
   */
  public function request(string $project_id, string $export_key, ?string $source, string $target, bool $poll, bool $resume, \Closure $may_queue): array
  {
    $key = $this->jobKey($project_id, $export_key, $source, $target);
    if ($poll) {
      return $this->state($key) ?? ['status' => 'failed'];
    }

    $lock = $this->locks->createLock($key);
    if (!$lock->acquire()) {
      return ['status' => 'queued'];
    }

    try {
      $state = $this->state($key);
      $status = $state['status'] ?? null;
      if ('queued' === $status || 'complete' === $status || 'source_required' === $status
        || ('partial' === $status && (!$resume || !($state['can_resume'] ?? false)))) {
        return $state;
      }
      if (!$may_queue()) {
        return ['status' => 'refused'];
      }

      $attempt = bin2hex(random_bytes(16));
      $this->save($key, ['status' => 'queued', 'attempt' => $attempt], self::JOB_TTL);
      try {
        $this->bus->dispatch(new TranslateCodeView($project_id, $export_key, $source, $target, $attempt));
      } catch (\Throwable $e) {
        $this->cache->deleteItem($key);
        throw $e;
      }

      return ['status' => 'queued'];
    } finally {
      $lock->release();
    }
  }

  #[AsMessageHandler]
  public function translate(TranslateCodeView $job): void
  {
    $key = $this->jobKey($job->project_id, $job->export_key, $job->source_language, $job->target_language);
    $lock = $this->locks->createLock($key.'.worker', self::MAX_JOB_SECONDS + 25);
    if (!$lock->acquire()) {
      return;
    }

    try {
      if ($job->attempt !== ($this->state($key)['attempt'] ?? null)) {
        return;
      }
      try {
        $project = $this->projects->find($job->project_id);
        $snapshot = null === $project ? null : $this->store->snapshot($project);
        $result = null !== $snapshot && $job->export_key === CodeViewExportStore::translationKey($snapshot)
          ? $this->translateExport($snapshot['data'], $job->project_id, $job->source_language, $job->target_language)
          : ['status' => 'failed'];
      } catch (UnsupportedLanguageException) {
        $result = ['status' => 'unsupported'];
      } catch (TranslationBudgetExceededException) {
        $result = ['status' => 'partial', 'translations' => [], 'can_resume' => true];
      } catch (\Throwable $e) {
        $this->logger->error('Code view translation failed', ['project' => $job->project_id, 'exception' => $e]);
        $result = ['status' => 'failed'];
      }
      if ($job->attempt === ($this->state($key)['attempt'] ?? null)) {
        $this->save($key, $result, 'complete' === $result['status'] ? self::TEXT_TTL : self::JOB_TTL);
      }
    } finally {
      $lock->release();
    }
  }

  /**
   * @param array<string, mixed> $export
   *
   * @return array<string, mixed>
   */
  private function translateExport(array $export, string $project_id, ?string $source, string $target): array
  {
    $deadline = microtime(true) + self::MAX_JOB_SECONDS;
    $texts = CodeViewTranslationContent::texts($export);
    if ([] === $texts) {
      return ['status' => 'complete', 'translations' => [], 'can_resume' => false];
    }

    if (null === $source) {
      $source = $this->detectSource($project_id, CodeViewTranslationContent::texts($export, include_names: false));
      if (null === $source) {
        return ['status' => 'source_required', 'translations' => [], 'can_resume' => false];
      }
    }

    $keys = [];
    foreach ($texts as $id => $text) {
      $keys[$id] = 'code_view_text.'.hash('sha256', $source."\0".$target."\0".$text);
    }
    $translations = [];
    $cache_misses = [];
    $long_texts = [];
    foreach ($this->cache->getItems(array_values(array_unique($keys))) as $cache_key => $item) {
      $cached = $item->get();
      if (is_string($cached)) {
        $translations[$cache_key] = $cached;
      }
    }
    foreach ($keys as $id => $key) {
      if (isset($translations[$key])) {
        continue;
      }
      if (isset($long_texts[$key])) {
        continue;
      }
      $text = $texts[$id];
      if (mb_strlen($text) <= self::MAX_BATCH_CHARACTERS) {
        $cache_misses[$key] = $text;
        continue;
      }
      foreach ($this->split($text) as [$piece, $separator]) {
        $piece_key = 'code_view_text.'.hash('sha256', $source."\0".$target."\0".$piece);
        $long_texts[$key][] = [$piece_key, $separator];
        if (!isset($translations[$piece_key])) {
          $cache_misses[$piece_key] = $piece;
        }
      }
    }
    if ([] !== $long_texts) {
      // Previous jobs may have completed pieces of a long note without finishing the note itself.
      foreach ($this->cache->getItems(array_keys($cache_misses)) as $key => $item) {
        $cached = $item->get();
        if (is_string($cached)) {
          $translations[$key] = $cached;
          unset($cache_misses[$key]);
        }
      }
    }

    $failed = false;
    foreach ($this->batches($cache_misses) as $batch) {
      $timeout = (int) floor($deadline - microtime(true));
      if ($timeout <= 0 || !$this->codeTranslationGlobalLimiter->create('site')->consume(count($batch))->isAccepted()) {
        break;
      }
      try {
        $translated = $this->provider->translateBatch(array_values($batch), $source, $target, $timeout);
      } catch (TranslationBudgetExceededException) {
        break;
      }
      if (null === $translated) {
        $failed = true;
        break;
      }
      foreach (array_keys($batch) as $i => $key) {
        $translation = '' === trim($translated[$i]) ? $batch[$key] : $translated[$i];
        $translations[$key] = $translation;
        unset($cache_misses[$key]);
        $this->save($key, $translation, self::TEXT_TTL);
      }
    }

    if ($failed && [] === $translations) {
      return ['status' => 'failed'];
    }
    foreach ($long_texts as $key => $pieces) {
      $translation = '';
      foreach ($pieces as [$piece_key, $separator]) {
        if (!isset($translations[$piece_key])) {
          continue 2;
        }
        $translation .= $translations[$piece_key].$separator;
      }
      $translations[$key] = $translation;
      $this->save($key, $translation, self::TEXT_TTL);
    }

    $updates = [];
    foreach ($keys as $id => $key) {
      if (isset($translations[$key]) && $texts[$id] !== $translations[$key]) {
        $updates[$id] = $translations[$key];
      }
    }

    return [
      'status' => [] !== $cache_misses ? 'partial' : 'complete',
      'source_language' => $source,
      'target_language' => $target,
      'translations' => CodeViewTranslatedNames::updates($export, $updates),
      'can_resume' => [] !== $cache_misses,
    ];
  }

  /** @return \Generator<int, array{string, string}> Text pieces and their original separators */
  private function split(string $text): \Generator
  {
    $offset = 0;
    $length = strlen($text);
    while ($length - $offset > self::MAX_BATCH_CHARACTERS) {
      $window = mb_strcut($text, $offset, self::MAX_BATCH_CHARACTERS, 'UTF-8');
      [$piece, $separator] = preg_match('/^(.*\S)(\s+)\S*$/su', $window, $match) ? [$match[1], $match[2]] : [$window, ''];
      yield [$piece, $separator];
      $offset += strlen($piece) + strlen($separator);
    }
    if ($offset < $length) {
      yield [substr($text, $offset), ''];
    }
  }

  /** @param array<string, string> $texts */
  private function detectSource(string $project_id, array $texts): ?string
  {
    $sample = '';
    foreach ($texts as $text) {
      $sample .= mb_substr(trim($text), 0, self::SAMPLE_CHARACTERS - 1 - mb_strlen($sample))."\n";
      if (mb_strlen($sample) >= self::SAMPLE_CHARACTERS - 1) {
        break;
      }
    }
    if (preg_match_all('/\p{L}/u', $sample) < 12) {
      return null;
    }
    $key = 'code_view_language.'.hash('sha256', $project_id."\0".$sample);
    $lock = $this->locks->createLock($key, 10);
    $lock->acquire(true);
    try {
      $item = $this->cache->getItem($key);
      if ($item->isHit()) {
        $source = $item->get();

        return is_string($source) ? $source : null;
      }
      if (!$this->codeTranslationGlobalLimiter->create('site')->consume()->isAccepted()) {
        throw new TranslationBudgetExceededException();
      }
      $detected = $this->provider->detectLanguage($sample);
      if (null === $detected) {
        throw new \RuntimeException('Code view language detection failed');
      }
      $source = $detected['confidence'] >= 60 && '' !== $detected['language'] ? $detected['language'] : null;
      $this->save($key, $source, self::TEXT_TTL);

      return $source;
    } finally {
      $lock->release();
    }
  }

  /**
   * @param array<string, string> $texts
   *
   * @return \Generator<int, array<string, string>>
   */
  private function batches(array $texts): \Generator
  {
    $batch = [];
    $characters = 0;
    $job_characters = 0;
    $job_texts = 0;
    foreach ($texts as $key => $text) {
      $length = mb_strlen($text);
      if ($job_texts >= self::MAX_JOB_TEXTS || $job_characters + $length > self::MAX_JOB_CHARACTERS) {
        break;
      }
      if (count($batch) >= self::MAX_BATCH_TEXTS || $characters + $length > self::MAX_BATCH_CHARACTERS) {
        yield $batch;
        $batch = [];
        $characters = 0;
      }
      $batch[$key] = $text;
      $characters += $length;
      $job_characters += $length;
      ++$job_texts;
    }
    if ([] !== $batch) {
      yield $batch;
    }
  }

  private function jobKey(string $project_id, string $export_key, ?string $source, string $target): string
  {
    return 'code_view_job.'.hash('sha256', implode("\0", [$project_id, $export_key, $source ?? '', $target]));
  }

  /**
   * @return array<string, mixed>|null
   *
   * @phpstan-impure The worker or another web request may update the shared cache.
   */
  private function state(string $key): ?array
  {
    $value = $this->cache->getItem($key)->get();

    return is_array($value) ? $value : null;
  }

  private function save(string $key, mixed $value, int $ttl): void
  {
    $this->cache->save($this->cache->getItem($key)->set($value)->expiresAfter($ttl));
  }
}
