<?php

namespace Catrobat\AppBundle\Exceptions;

class InvalidStorageDirectoryException extends \RuntimeException
{
  /*
 * (non-PHPdoc) @see RuntimeException::__construct()
 */
  public function __construct($message)
  {
    parent::__construct($message);
  }
}
