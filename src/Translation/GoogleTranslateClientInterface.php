<?php

declare(strict_types=1);

namespace App\Translation;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Translate\V3\TranslateTextResponse;

interface GoogleTranslateClientInterface
{
  /**
   * @throws ApiException
   * @throws ValidationException when the client cannot be built, e.g. without Google credentials
   */
  public function translateText(array $contents, string $targetLanguageCode, string $parent, ?string $sourceLanguageCode = null, string $mimeType = 'text/plain'): TranslateTextResponse;
}
