<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\InvalidProgramUploadedEvent;
use Psr\Log\LoggerInterface;

/**
 * Class InvalidProgramUploadLogger
 * @package App\Catrobat\Listeners
 */
class InvalidProgramUploadLogger
{

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * InvalidProgramUploadLogger constructor.
   *
   * @param LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger)
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
