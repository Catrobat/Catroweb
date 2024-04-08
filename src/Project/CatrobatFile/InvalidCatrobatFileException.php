<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

class InvalidCatrobatFileException extends \RuntimeException
{
  public function __construct(string $message, int $code, private readonly string $debug_message = '')
  {
    parent::__construct($message, $code);
  }

  public function getStatusCode(): int|string
  {
    return $this->getCode();
  }

  public function getDebugMessage(): string
  {
    return $this->debug_message;
  }
}
