<?php

declare(strict_types=1);

namespace App\Api\Services;

/**
 * Class ValidationWrapper.
 */
class ValidationWrapper
{
  private array $errors = [];

  public function addError(string $value, ?string $key = null): ValidationWrapper
  {
    if (!is_null($key)) {
      $this->errors[$key] = $value;
    } else {
      $this->errors[] = $value;
    }

    return $this;
  }

  public function hasError(): bool
  {
    return [] !== $this->getErrors();
  }

  public function getError(?string $key = null): string
  {
    return $this->hasError() ? (null !== $key ? $this->errors[$key] : $this->errors[0]) : '';
  }

  public function clear(): void
  {
    $this->errors = [];
  }

  public function getErrors(): array
  {
    return $this->errors;
  }
}
