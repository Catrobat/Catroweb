<?php

namespace App\Translation;

use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;
use Psr\Log\LoggerInterface;

class GoogleTranslateApi implements TranslationApiInterface
{
  private const LONG_LANGUAGE_CODE = [
    'zh-CN',
    'zh-TW',
  ];

  private TranslateClient $client;
  private LoggerInterface $logger;
  private TranslationApiHelper $helper;

  public function __construct(TranslateClient $client, LoggerInterface $logger)
  {
    $this->client = $client;
    $this->logger = $logger;
    $this->helper = new TranslationApiHelper(self::LONG_LANGUAGE_CODE);
  }

  public function translate(string $text, ?string $source_language, string $target_language): ?TranslationResult
  {
    $target_language = $this->helper->transformLanguageCode($target_language);
    $source_language = $this->helper->transformLanguageCode($source_language);

    try {
      $result = $this->client->translate($text, [
        'source' => $source_language,
        'target' => $target_language,
        'format' => 'text',
      ]);
    } catch (ServiceException $e) {
      $this->logger->error("Google translate client exception, source: {$source_language}, target: {$target_language}, text: {$text}, message: {$e->getMessage()}");

      return null;
    }

    if (null === $result) {
      return null;
    }

    $translation_result = new TranslationResult();
    $translation_result->provider = 'google';
    if (null == $source_language) {
      $translation_result->detected_source_language = $result['source'];
    }
    $translation_result->translation = $result['text'];

    return $translation_result;
  }
}
