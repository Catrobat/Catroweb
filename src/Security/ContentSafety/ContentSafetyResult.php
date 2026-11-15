<?php

declare(strict_types=1);

namespace App\Security\ContentSafety;

class ContentSafetyResult
{
  public function __construct(
    public readonly bool $safe,
    public readonly float $nsfwScore = 0.0,
    public readonly string $label = 'unknown',
    public readonly bool $skipped = false,
    public readonly bool $unavailable = false,
    public readonly bool $invalid = false,
  ) {
  }

  /** The scanner refused the image (4xx, e.g. a format it can't read): that image only, the scanner works. */
  public static function invalid(): self
  {
    return new self(safe: true, invalid: true);
  }

  public static function skipped(): self
  {
    return new self(safe: true, skipped: true);
  }

  public static function unavailable(): self
  {
    return new self(safe: true, unavailable: true);
  }
}
