<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Enum\ContentType;

class ReportException extends \RuntimeException
{
  public const int CODE_NOT_FOUND = 404;
  public const int CODE_INVALID_CATEGORY = 400;
  public const int CODE_TRUST_TOO_LOW = 403;
  public const int CODE_OWN_CONTENT = 403;
  public const int CODE_WHITELISTED = 403;
  public const int CODE_ALREADY_HIDDEN = 409;
  public const int CODE_DUPLICATE = 409;
  public const int CODE_RATE_LIMITED = 429;
  public const int CODE_EMAIL_NOT_VERIFIED = 403;

  private function __construct(string $message, int $code)
  {
    parent::__construct($message, $code);
  }

  public static function contentNotFound(): self
  {
    return new self('Content not found.', self::CODE_NOT_FOUND);
  }

  public static function invalidCategory(string $category, ContentType $content_type): self
  {
    return new self(
      sprintf('Invalid category "%s" for content type "%s".', $category, $content_type->value),
      self::CODE_INVALID_CATEGORY,
    );
  }

  public static function trustTooLow(float $current, float $required): self
  {
    return new self(
      sprintf('Your trust score (%.1f) is below the minimum required (%.1f) to file reports.', $current, $required),
      self::CODE_TRUST_TOO_LOW,
    );
  }

  public static function cannotReportOwnContent(): self
  {
    return new self('You cannot report your own content.', self::CODE_OWN_CONTENT);
  }

  public static function contentWhitelisted(): self
  {
    return new self('This content is whitelisted and cannot be reported.', self::CODE_WHITELISTED);
  }

  public static function contentAlreadyHidden(): self
  {
    return new self('This content is already hidden.', self::CODE_ALREADY_HIDDEN);
  }

  public static function duplicateReport(): self
  {
    return new self('You have already reported this content.', self::CODE_DUPLICATE);
  }

  public static function rateLimited(): self
  {
    return new self("You're submitting reports too quickly. Please wait and try again.", self::CODE_RATE_LIMITED);
  }

  public static function emailNotVerified(): self
  {
    return new self('Email verification required to report content.', self::CODE_EMAIL_NOT_VERIFIED);
  }
}
