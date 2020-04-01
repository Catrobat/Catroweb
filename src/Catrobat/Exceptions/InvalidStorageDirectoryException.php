<?php

namespace App\Catrobat\Exceptions;

use RuntimeException;

class InvalidStorageDirectoryException extends RuntimeException
{
  public function __construct(string $message)
  {
    parent::__construct($message);
  }
}
