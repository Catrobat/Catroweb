<?php

declare(strict_types=1);

namespace App\Api\Services\Translation;

use App\Api\Services\Base\AbstractRequestValidator;

class TranslationRequestValidator extends AbstractRequestValidator
{
  private const array VALID_FIELDS = ['name', 'description', 'credit'];

  public function isValidField(string $field): bool
  {
    return in_array($field, self::VALID_FIELDS, true);
  }
}
