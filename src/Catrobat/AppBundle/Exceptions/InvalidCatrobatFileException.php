<?php

namespace Catrobat\AppBundle\Exceptions;

class InvalidCatrobatFileException extends \RuntimeException
{
  private $debug_message;

  /*
   * (non-PHPdoc) @see RuntimeException::__construct()
   */
  public function __construct($message, $code, $debug_message = "")
  {
    parent::__construct($message, $code);
    $this->debug_message = $debug_message;
  }

  public function getStatusCode()
  {
    return $this->getCode();
  }

  public function getDebugMessage()
  {
    return $this->debug_message;
  }
}
