<?php

declare(strict_types=1);

namespace App\Translation;

use Google\ApiCore\ApiException;
use Google\Cloud\Translate\V3\TranslateTextResponse;

interface GoogleTranslateClientInterface
{
  /**
   * @throws ApiException
   */
  public function translateText(array $contents, string $targetLanguageCode, string $parent, ?string $sourceLanguageCode = null, string $mimeType = 'text/plain'): TranslateTextResponse;
}
