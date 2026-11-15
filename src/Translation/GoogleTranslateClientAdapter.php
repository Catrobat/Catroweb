<?php

declare(strict_types=1);

namespace App\Translation;

use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Google\Cloud\Translate\V3\TranslateTextResponse;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

class GoogleTranslateClientAdapter implements GoogleTranslateClientInterface
{
  /**
   * The client is built on first use: its constructor throws a ValidationException without Google credentials,
   * which must not take down every service that depends on the translation delegate.
   *
   * @param \Closure(): TranslationServiceClient $client
   */
  public function __construct(
    #[AutowireServiceClosure(TranslationServiceClient::class)]
    private readonly \Closure $client,
  ) {
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

    return ($this->client)()->translateText($request);
  }
}
