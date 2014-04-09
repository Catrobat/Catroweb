<?php
namespace Catrobat\CoreBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Symfony\Component\HttpFoundation\File\File;

class InvalidProjectUploadedEvent extends Event
{
  
  protected $file;
  
  function __construct(File $file)
  {
    $this->file = $file;
  }

  public function getFile()
  {
    return $this->file;
  }
}