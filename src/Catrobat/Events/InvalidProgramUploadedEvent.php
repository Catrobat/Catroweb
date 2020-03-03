<?php

namespace App\Catrobat\Events;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class InvalidProgramUploadedEvent.
 */
class InvalidProgramUploadedEvent extends Event
{
  /**
   * @var File
   */
  protected $file;
  /**
   * @var InvalidCatrobatFileException
   */
  protected $exception;

  /**
   * InvalidProgramUploadedEvent constructor.
   */
  public function __construct(File $file, InvalidCatrobatFileException $exception)
  {
    $this->file = $file;
    $this->exception = $exception;
  }

  /**
   * @return File
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * @return InvalidCatrobatFileException
   */
  public function getException()
  {
    return $this->exception;
  }
}
