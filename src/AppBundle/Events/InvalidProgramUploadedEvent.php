<?php
namespace AppBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Exceptions\InvalidCatrobatFileException;

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