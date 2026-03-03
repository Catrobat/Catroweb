<?php

declare(strict_types=1);

namespace App\Moderation;

class AppealException extends \RuntimeException
{
  public const int CODE_NOT_FOUND = 404;
  public const int CODE_NOT_HIDDEN = 400;
  public const int CODE_NOT_OWNER = 403;
  public const int CODE_ALREADY_PENDING = 409;
  public const int CODE_REASON_REQUIRED = 400;

  private function __construct(string $message, int $code)
  {
    parent::__construct($message, $code);
  }

  public static function contentNotFound(): self
  {
    return new self('Content not found.', self::CODE_NOT_FOUND);
  }

  public static function contentNotHidden(): self
  {
    return new self('Content is not hidden, no appeal necessary.', self::CODE_NOT_HIDDEN);
  }

  public static function notOwner(): self
  {
    return new self('Only the content owner can file an appeal.', self::CODE_NOT_OWNER);
  }

  public static function appealAlreadyExists(): self
  {
    return new self('You have already filed an appeal for this content.', self::CODE_ALREADY_PENDING);
  }

  public static function reasonRequired(): self
  {
    return new self('An appeal reason is required.', self::CODE_REASON_REQUIRED);
  }
}
