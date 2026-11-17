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
   */
  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    if ('' === trim($text)) {
      $result = new TranslationResult();
      $result->translation = $text;

      return $result;
    }

    if (!$this->translationGlobalLimiter->create('site')->consume()->isAccepted()) {
      return $this->failed(new \RuntimeException('site-wide translation limit reached'), $text, $source_language, $target_language);
    }

    try {
      // One text per request: with "auto", LibreTranslate detects a single language for a whole batch.
      $data = $this->http_client->request('POST', $this->url.'/translate', [
        'json' => [
          'q' => $text,
          'source' => null === $source_language ? 'auto' : $this->toLibreTranslate($source_language),
          'target' => $this->toLibreTranslate($target_language),
          'format' => 'text',
        ],
        // A project is up to three calls in one web request.
        'timeout' => 5,
        'max_duration' => 5,
      ])->toArray();
      $translation = $data['translatedText'] ?? null;
      if (!is_string($translation)) {
        throw new \UnexpectedValueException('response has no translatedText');
      }
    } catch (ClientExceptionInterface $e) {
      try {
        $unsupported = 400 === $e->getResponse()->getStatusCode() && str_contains($e->getResponse()->getContent(false), 'is not supported');
      } catch (ExceptionInterface) {
        $unsupported = false;
      }

      if ($unsupported) {
        // A language LibreTranslate has no model for: not an outage. Other 4xx (403, 404, 429...) are.
        $this->logger->notice(sprintf('Machine translation rejected: %s, source: %s, target: %s', $e->getMessage(), $source_language ?? 'auto', $target_language));

        return null;
      }

      return $this->failed($e, $text, $source_language, $target_language);
    } catch (ExceptionInterface|\UnexpectedValueException $e) {
      return $this->failed($e, $text, $source_language, $target_language);
    }

    $result = new TranslationResult();
    $result->translation = $translation;
    $detected = $data['detectedLanguage']['language'] ?? null;
    if (null === $source_language && is_string($detected)) {
      $result->detected_source_language = $this->fromLibreTranslate($detected);
    }

    return $result;
  }

  private function failed(\Throwable $e, string $text, ?string $source_language, string $target_language): null
  {
    // The infra watchdog counts this exact message. Error level: prod's fingers_crossed handler drops warnings.
    $this->logger->error(sprintf(
      'Machine translation failed: %s, source: %s, target: %s, text length: %d',
      $e->getMessage(), $source_language ?? 'auto', $target_language, mb_strlen($text),
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
        // Rendered into every project page: retry soon, but don't make each page wait for a dead server.
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

  private function toLibreTranslate(string $language): string
  {
    $short = substr($language, 0, 2);

    return self::LANGUAGE_CODES[$language] ?? self::LANGUAGE_CODES[$short] ?? $short;
  }

  private function fromLibreTranslate(string $language): string
  {
    return array_flip(self::LANGUAGE_CODES)[$language] ?? $language;
  }
}
