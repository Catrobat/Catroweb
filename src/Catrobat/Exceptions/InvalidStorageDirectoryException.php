<?php

namespace App\Catrobat\Exceptions;

/**
 * Class InvalidStorageDirectoryException
 * @package App\Catrobat\Exceptions
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
