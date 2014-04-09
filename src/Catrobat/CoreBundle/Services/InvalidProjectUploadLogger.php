<?php
namespace Catrobat\CoreBundle\Services;

use Catrobat\CoreBundle\Events\InvalidProjectUploadedEvent;
use Monolog\Logger;

class InvalidProjectUploadLogger
{
  private $logger;

  function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }

  function onInvalidProjectUploadedEvent(InvalidProjectUploadedEvent $event)
  {
    $this->logger->error("Invalid File: " . $event->getFile()->getFilename());
  }
}
