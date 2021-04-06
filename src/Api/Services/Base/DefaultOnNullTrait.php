<?php

namespace App\Api\Services\Base;

/**
 * Trait DefaultOnNullTrait.
 *
 * Currently default values are not working with our OpenAPI Generator (v5).
 * Therefore, in the case that no value for a parameter is provided the variable is automatically set to null.
 * The methods defined in this section overwrite this null values with their default values if one should exist.
 * In case an API route has no default value defined, the value should at least be casted to the correct type.
 *
 * In case you change the default values make sure to adapt all method calls to overwrite the new default.
 */
trait DefaultOnNullTrait
{
  protected function getDefaultLimitOnNull(?int $limit, ?int $overwrite_default = null): int
  {
    return $limit ?? $overwrite_default ?? 20;
  }

  protected function getDefaultOffsetOnNull(?int $offset, ?int $overwrite_default = null): int
  {
    return $offset ?? $overwrite_default ?? 0;
  }

  protected function getDefaultAcceptLanguageOnNull(?string $accept_language, ?string $overwrite_default = null): string
  {
    return $accept_language ?? $overwrite_default ?? 'en';
  }

  protected function getDefaultMaxVersionOnNull(?string $max_version, ?string $overwrite_default = null): string
  {
    return $max_version ?? $overwrite_default ?? '';
  }

  protected function getDefaultFlavorOnNull(?string $flavor, ?string $overwrite_default = null): string
  {
    return $flavor ?? $overwrite_default ?? '';
  }

  protected function getDefaultPlatformOnNull(?string $platform, ?string $overwrite_default = null): string
  {
    return $platform ?? $overwrite_default ?? '';
  }
}
