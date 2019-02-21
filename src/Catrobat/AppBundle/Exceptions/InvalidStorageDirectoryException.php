<?php

namespace Catrobat\AppBundle\Exceptions;

/**
 * Class InvalidStorageDirectoryException
 * @package Catrobat\AppBundle\Exceptions
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
