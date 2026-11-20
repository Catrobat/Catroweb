<?php

declare(strict_types=1);

namespace App\Translation;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The machine translation provider: a self-hosted LibreTranslate.
 */
class LibreTranslateApi
{
  public const string PROVIDER = 'libretranslate';

  // Catroweb codes LibreTranslate spells differently; other codes are cut to their 2-letter language.
  private const array LANGUAGE_CODES = ['zh-CN' => 'zh-Hans', 'zh-TW' => 'zh-Hant', 'pt-BR' => 'pt-BR', 'no' => 'nb'];

  public function __construct(
    private readonly HttpClientInterface $http_client,
    private readonly CacheInterface $cache,
    private readonly LoggerInterface $logger,
    #[Autowire(env: 'LIBRETRANSLATE_URL')]
    private readonly string $url,
    // Site-wide, one token per LibreTranslate call: it runs on the web host's CPU.
    private readonly RateLimiterFactoryInterface $translationGlobalLimiter,
  ) {
  }

  /**
   * @param string|null $source_language null to auto-detect
   *
   * @throws UnsupportedLanguageException
   */
  public function translate(string $text, ?string $source_language, string $target_language, int $timeout = 5): ?TranslationResult
  {
    if ('' === trim($text)) {
      $result = new TranslationResult();
      $result->translation = $text;

      return $result;
    }

    $data = $this->request('translate', $text, $source_language, $target_language, $timeout);
    if (null === $data) {
      return null;
    }
    $translation = $data['translatedText'] ?? null;
    if (!is_string($translation)) {
      return $this->failed(new \UnexpectedValueException('response has no translatedText'), mb_strlen($text), $source_language, $target_language);
    }

    $result = new TranslationResult();
    $result->translation = $translation;
    $detected = $data['detectedLanguage']['language'] ?? null;
    if (null === $source_language && is_string($detected)) {
      $result->detected_source_language = $this->fromLibreTranslate($detected);
    }

    return $result;
  }

  /**
   * @return array{language: string, confidence: float}|null
   */
  public function detectLanguage(string $sample): ?array
  {
    $data = $this->request('detect', $sample, null, null, 5, defer_when_limited: true);
    if (null === $data) {
      return null;
    }

    $top = $data[0] ?? null;
    $language = $top['language'] ?? '';
    $confidence = $top['confidence'] ?? 0;

    return [
      'language' => is_string($language) ? $this->fromLibreTranslate($language) : '',
      'confidence' => is_numeric($confidence) ? (float) $confidence : 0.0,
    ];
  }

  /**
   * @param list<string> $texts
   *
   * @return list<string>|null
   */
  public function translateBatch(array $texts, string $source_language, string $target_language, int $timeout): ?array
  {
    if (self::isSameLanguage($source_language, $target_language)) {
      return $texts;
    }

    $data = $this->request('translate', $texts, $source_language, $target_language, $timeout, defer_when_limited: true);
    if (null === $data) {
      return null;
    }

    $translations = $data['translatedText'] ?? null;
    if (!is_array($translations) || !array_is_list($translations) || count($translations) !== count($texts)
      || count(array_filter($translations, is_string(...))) !== count($texts)) {
      return $this->failed(new \UnexpectedValueException('response has an invalid translatedText list'), array_sum(array_map(mb_strlen(...), $texts)), $source_language, $target_language);
    }

    return $translations;
  }

  /**
   * @param string|list<string> $texts
   *
   * @return array<array-key, mixed>|null
   */
  private function request(string $endpoint, string|array $texts, ?string $source, ?string $target, int $timeout, bool $defer_when_limited = false): ?array
  {
    $length = is_string($texts) ? mb_strlen($texts) : array_sum(array_map(mb_strlen(...), $texts));
    if (!$this->translationGlobalLimiter->create('site')->consume()->isAccepted()) {
      if ($defer_when_limited) {
        throw new TranslationBudgetExceededException();
      }

      return $this->failed(new \RuntimeException('site-wide translation limit reached'), $length, $source, $target);
    }

    $body = ['q' => $texts];
    if (null !== $target) {
      $body += [
        'source' => null === $source ? 'auto' : self::toLibreTranslate($source),
        'target' => self::toLibreTranslate($target),
        'format' => 'text',
      ];
    }

    try {
      return $this->http_client->request('POST', $this->url.'/'.$endpoint, [
        'json' => $body,
        'timeout' => $timeout,
        'max_duration' => $timeout,
      ])->toArray();
    } catch (ClientExceptionInterface $e) {
      try {
        $response = $e->getResponse();
        $error = $response->getContent(false);
        $unsupported = 400 === $response->getStatusCode()
          && (str_contains($error, 'is not supported') || str_contains($error, 'is not available as a target language from'));
      } catch (ExceptionInterface) {
        $unsupported = false;
      }

      if ($unsupported) {
        $this->logger->notice(sprintf('Machine translation rejected: %s, source: %s, target: %s', $e->getMessage(), $source ?? 'auto', $target ?? ''));
        throw new UnsupportedLanguageException($e->getMessage(), 0, $e);
      }

      return $this->failed($e, $length, $source, $target);
    } catch (ExceptionInterface $e) {
      return $this->failed($e, $length, $source, $target);
    }
  }

  private function failed(\Throwable $e, int $length, ?string $source_language, ?string $target_language): null
  {
    // The infra watchdog counts this exact message. Error level: prod's fingers_crossed handler drops warnings.
    $this->logger->error(sprintf(
      'Machine translation failed: %s, source: %s, target: %s, text length: %d',
      $e->getMessage(), $source_language ?? 'auto', $target_language ?? '', $length,
    ));

    return null;
  }

  /**
   * @return string[] Catroweb codes LibreTranslate translates; empty while it is unreachable
   */
  public function getSupportedLanguages(): array
  {
    return $this->cache->get('libretranslate_languages', function (ItemInterface $item): array {
      $item->expiresAfter(86400);
      try {
        $languages = $this->http_client->request('GET', $this->url.'/languages', ['timeout' => 3, 'max_duration' => 3])->toArray();
      } catch (ExceptionInterface $e) {
        // Asked on a page's first translate click: retry soon, but don't make each click wait for a dead server.
        $item->expiresAfter(300);
        $this->logger->error('LibreTranslate languages unavailable: '.$e->getMessage());

        return [];
      }

      $codes = array_map($this->fromLibreTranslate(...), array_filter(array_column($languages, 'code'), is_string(...)));
      if ([] === $codes) {
        $item->expiresAfter(300);
        $this->logger->error('LibreTranslate languages unavailable: empty or malformed /languages response');
      }

      return array_values($codes);
    });
  }

  public static function isSameLanguage(string $source, string $target): bool
  {
    return self::toLibreTranslate($source) === self::toLibreTranslate($target);
  }

  private static function toLibreTranslate(string $language): string
  {
    $short = substr($language, 0, 2);

    return self::LANGUAGE_CODES[$language] ?? self::LANGUAGE_CODES[$short] ?? $short;
  }

  private function fromLibreTranslate(string $language): string
  {
    return array_flip(self::LANGUAGE_CODES)[$language] ?? $language;
  }
}
