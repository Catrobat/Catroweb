<?php

namespace Catrobat\AppBundle\Exceptions;

class InvalidCatrobatFileException extends \RuntimeException
{
    /*
   * (non-PHPdoc) @see RuntimeException::__construct()
  */
  public function __construct($message, $code = 500)
  {
      parent::__construct($message, $code);
  }

    public function getStatusCode()
    {
        return $this->getCode();
    }
}
