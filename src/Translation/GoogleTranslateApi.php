<?php

declare(strict_types=1);

namespace App\Translation;

use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;
use Psr\Log\LoggerInterface;

class GoogleTranslateApi implements TranslationApiInterface
{
  private const array LONG_LANGUAGE_CODE = [
    'zh-CN',
    'zh-TW',
  ];

  private const array SUPPORTED_LANGUAGE_CODE = [
    'af',
    'am',
    'ar',
    'az',
    'be',
    'bg',
    'bn',
    'bs',
    'ca',
    'co',
    'cs',
    'cy',
    'da',
    'de',
    'el',
    'en',
    'eo',
    'es',
    'et',
    'eu',
    'fa',
    'fi',
    'fr',
    'fy',
    'ga',
    'gd',
    'gl',
    'gu',
    'ha',
    'he',
    'hi',
    'hr',
    'ht',
    'hu',
    'hy',
    'id',
    'ig',
    'is',
    'it',
    'ja',
    'jv',
    'ka',
    'kk',
    'km',
    'kn',
    'ko',
    'ku',
    'ky',
    'lb',
    'lo',
    'lt',
    'lv',
    'mg',
    'mi',
    'mk',
    'ml',
    'mn',
    'mr',
    'ms',
    'mt',
    'my',
    'ne',
    'nl',
    'no',
    'ny',
    'or',
    'pa',
    'pl',
    'ps',
    'pt',
    'ro',
    'ru',
    'rw',
    'sd',
    'si',
    'sk',
    'sl',
    'sm',
    'sn',
    'so',
    'sq',
    'sr',
    'st',
    'su',
    'sv',
    'sw',
    'ta',
    'te',
    'tg',
    'th',
    'tk',
    'tl',
    'tr',
    'tt',
    'ug',
    'uk',
    'ur',
    'ur',
    'uz',
    'vi',
    'xh',
    'yi',
    'yo',
    'zh',
    'zh-CN',
    'zh-TW',
    'zu',
  ];

  private readonly TranslationApiHelper $helper;

  public function __construct(private readonly TranslateClient $client, private readonly LoggerInterface $logger, private readonly int $short_text_length)
  {
    $this->helper = new TranslationApiHelper(self::LONG_LANGUAGE_CODE);
  }

  #[\Override]
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
    } catch (ServiceException $serviceException) {
      $this->logger->error(sprintf('Google translate client exception, source: %s, target: %s, text: %s, message: %s', $source_language, $target_language, $text, $serviceException->getMessage()));

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

  #[\Override]
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

    if (mb_strlen($text) <= $this->short_text_length) {
      return 1;
    }

    return 0.5;
  }
}
