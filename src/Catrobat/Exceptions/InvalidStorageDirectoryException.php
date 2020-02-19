<?php

namespace App\Catrobat\Exceptions;

/**
 * Class InvalidStorageDirectoryException.
 */
class InvalidStorageDirectoryException extends \RuntimeException
{
  /**
   * InvalidStorageDirectoryException constructor.
   *
   * @param $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
}
