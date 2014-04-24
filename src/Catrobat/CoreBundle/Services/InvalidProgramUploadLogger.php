<?php
namespace Catrobat\CoreBundle\Services;

use Catrobat\CoreBundle\Events\InvalidProgramUploadedEvent;
use Monolog\Logger;

class InvalidProgramUploadLogger
{
  private $logger;

  function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }

  function onInvalidProgramUploadedEvent(InvalidProgramUploadedEvent $event)
  {
    $this->logger->error("Invalid File: " . $event->getFile()->getFilename());
  }
}
