<?php

namespace App\Catrobat\Events;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

class InvalidProgramUploadedEvent extends Event
{
  protected File $file;

  protected InvalidCatrobatFileException $exception;

  public function __construct(File $file, InvalidCatrobatFileException $exception)
  {
    $this->file = $file;
    $this->exception = $exception;
  }

  public function getFile(): File
  {
    return $this->file;
  }

  public function getException(): InvalidCatrobatFileException
  {
    return $this->exception;
  }
}
