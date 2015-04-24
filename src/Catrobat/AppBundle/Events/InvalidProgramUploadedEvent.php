<?php
namespace Catrobat\AppBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;

class InvalidProgramUploadedEvent extends Event
{
  
  protected $file;
  protected $exception;
  
  function __construct(File $file, InvalidCatrobatFileException $exception)
  {
    $this->file = $file;
    $this->exception = $exception;
  }

  public function getFile()
  {
    return $this->file;
  }
  
  public function getException()
  {
    return $this->exception;
  }
}