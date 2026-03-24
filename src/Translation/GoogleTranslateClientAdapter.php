<?php

declare(strict_types=1);

namespace App\Translation;

use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Google\Cloud\Translate\V3\TranslateTextResponse;

class GoogleTranslateClientAdapter implements GoogleTranslateClientInterface
{
  public function __construct(private readonly TranslationServiceClient $client)
  {
  }

  #[\Override]
  public function translateText(array $contents, string $targetLanguageCode, string $parent, ?string $sourceLanguageCode = null, string $mimeType = 'text/plain'): TranslateTextResponse
  {
    $request = new TranslateTextRequest();
    $request->setContents($contents);
    $request->setTargetLanguageCode($targetLanguageCode);
    $request->setParent($parent);
    $request->setMimeType($mimeType);

    if (null !== $sourceLanguageCode) {
      $request->setSourceLanguageCode($sourceLanguageCode);
    }

    return $this->client->translateText($request);
  }
}
