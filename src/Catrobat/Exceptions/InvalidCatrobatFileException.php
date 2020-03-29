<?php

namespace App\Catrobat\Exceptions;

use RuntimeException;

class InvalidCatrobatFileException extends RuntimeException
{
  private string $debug_message;

  public function __construct(string $message, int $code, string $debug_message = '')
  {
    parent::__construct($message, $code);
    $this->debug_message = $debug_message;
  }

  public function getStatusCode(): int
  {
    return $this->getCode();
  }

  public function getDebugMessage(): string
  {
    return $this->debug_message;
  }
}
