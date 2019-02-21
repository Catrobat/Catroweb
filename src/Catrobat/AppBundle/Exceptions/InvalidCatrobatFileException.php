<?php

namespace Catrobat\AppBundle\Exceptions;

/**
 * Class InvalidCatrobatFileException
 * @package Catrobat\AppBundle\Exceptions
 */
class InvalidCatrobatFileException extends \RuntimeException
{
  /**
   * @var string
   */
  private $debug_message;


  /**
   * InvalidCatrobatFileException constructor.
   *
   * @param        $message
   * @param        $code
   * @param string $debug_message
   */
  public function __construct($message, $code, $debug_message = "")
  {
    parent::__construct($message, $code);
    $this->debug_message = $debug_message;
  }

  /**
   * @return int|mixed
   */
  public function getStatusCode()
  {
    return $this->getCode();
  }

  /**
   * @return string
   */
  public function getDebugMessage()
  {
    return $this->debug_message;
  }
}
