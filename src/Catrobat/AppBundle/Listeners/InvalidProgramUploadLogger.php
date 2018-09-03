<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\InvalidProgramUploadedEvent;
use Monolog\Logger;

class InvalidProgramUploadLogger
{

  private $logger;

  public function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }

  public function onInvalidProgramUploadedEvent(InvalidProgramUploadedEvent $event)
  {
    $this->logger->error('Invalid File: ' . $event->getFile()
        ->getFilename() . ' Exception: ' . $event->getException()
        ->getMessage() . ' Debug: ' . $event->getException()
        ->getDebugMessage());
  }
}
