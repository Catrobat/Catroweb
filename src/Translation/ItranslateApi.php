<?php

declare(strict_types=1);

namespace App\Translation;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ItranslateApi implements TranslationApiInterface
{
  private const LONG_LANGUAGE_CODE = [
    'zh-CN',
    'zh-TW',
    'pt-BR',
    'pt-PT',
  ];

  private const SUPPORTED_LANGUAGE_CODE = [
    'af',
    'ar',
    'az',
    'bg',
    'bn',
    'bs',
    'cs',
    'da',
    'de',
    'el',
    'en',
    'es',
    'et',
    'fa',
    'fi',
    'fr',
    'he',
    'hi',
    'hr',
    'hu',
    'id',
    'is',
    'it',
    'ja',
    'ka',
    'ko',
    'lt',
    'lv',
    'mk',
    'mn',
    'ms',
    'my',
    'ne',
    'nl',
    'no',
    'pl',
    'pt-BR',
    'pt-PT',
    'ro',
    'ru',
    'sk',
    'sl',
    'so',
    'sq',
    'sr',
    'sv',
    'sw',
    'ta',
    'th',
    'tl',
    'tr',
    'uk',
    'ur',
    'vi',
    'zh-CN',
    'zh-TW',
  ];
  private readonly string $api_key;
  private readonly TranslationApiHelper $helper;

  public function __construct(private readonly Client $client, private readonly LoggerInterface $logger)
  {
    $this->api_key = $_ENV['ITRANSLATE_API_KEY'];
    $this->helper = new TranslationApiHelper(self::LONG_LANGUAGE_CODE);
  }

  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $target_language = $this->helper->transformLanguageCode($target_language);
    $source_language = $this->helper->transformLanguageCode($source_language);

    try {
      $response = $this->client->request(
        'POST',
        '/translate/v1',
        [
          'json' => [
            'key' => $this->api_key,
            'source' => [
              'dialect' => empty($source_language) ? 'auto' : $source_language,
              'text' => $text,
            ],
            'target' => [
              'dialect' => $target_language,
            ],
          ],
        ]
      );
    } catch (GuzzleException $e) {
      $this->logger->error("Itranslate Guzzle client exception, source: {$source_language}, target: {$target_language}, text: {$text}, message: {$e->getMessage()}");

      return null;
    }

    $statusCode = $response->getStatusCode();

    if (200 != $statusCode) {
      $this->logger->error("Itranslate returned status code {$statusCode}, source: {$source_language}, target: {$target_language}, text: {$text}, body: {$response->getBody()}");

      return null;
    }

    $body = $response->getBody()->getContents();
    $result = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

    $translation_result = new TranslationResult();
    $translation_result->provider = 'itranslate';
    if (null == $source_language) {
      $translation_result->detected_source_language = $result['source']['detected'];
    }
    $translation_result->translation = $result['target']['text'];

    return $translation_result;
  }

  public function getPreference(string $text, ?string $source_language, string $target_language): float
  {
    $target_language = $this->helper->transformLanguageCode($target_language);
    $source_language = $this->helper->transformLanguageCode($source_language);

    if (null !== $source_language && !in_array($source_language, self::SUPPORTED_LANGUAGE_CODE, true)) {
      return 0;
    }

    if (!in_array($target_language, self::SUPPORTED_LANGUAGE_CODE, true)) {
      return 0;
    }

    return 0.5;
  }
}
