<?php

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

  private Client $client;
  private string $api_key;
  private LoggerInterface $logger;

  public function __construct(Client $client, LoggerInterface $logger)
  {
    $this->client = $client;
    $this->logger = $logger;
    $this->api_key = $_ENV['ITRANSLATE_API_KEY'];
  }

  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $target_language = $this->transformLanguageCode($target_language);
    $source_language = $this->transformLanguageCode($source_language);

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
    $result = json_decode($body, true);

    $translation_result = new TranslationResult();
    $translation_result->provider = 'itranslate';
    if (null == $source_language) {
      $translation_result->detected_source_language = $result['source']['detected'];
    }
    $translation_result->translation = $result['target']['text'];

    return $translation_result;
  }

  private function transformLanguageCode(?string $language): ?string
  {
    if (null === $language) {
      return null;
    }

    if (2 == strlen($language)) {
      return $language;
    }

    if (in_array($language, self::LONG_LANGUAGE_CODE, true)) {
      return $language;
    }

    return substr($language, 0, 2);
  }
}
