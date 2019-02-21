<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\InvalidProgramUploadedEvent;
use Monolog\Logger;

/**
 * Class InvalidProgramUploadLogger
 * @package Catrobat\AppBundle\Listeners
 */
class InvalidProgramUploadLogger
{

  /**
   * @var Logger
   */
  private $logger;

  /**
   * InvalidProgramUploadLogger constructor.
   *
   * @param Logger $logger
   */
  public function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }

  /**
   * @param InvalidProgramUploadedEvent $event
   */
  public function onInvalidProgramUploadedEvent(InvalidProgramUploadedEvent $event)
  {
    $this->logger->error('Invalid File: ' . $event->getFile()
        ->getFilename() . ' Exception: ' . $event->getException()
        ->getMessage() . ' Debug: ' . $event->getException()
        ->getDebugMessage());
  }
}
