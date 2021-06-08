<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\MissingProgramNameException;
use App\Catrobat\Exceptions\Upload\NameTooLongException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use Symfony\Component\HttpFoundation\Response;

class NameValidator
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if ('' == $file->getName()) {
      throw new MissingProgramNameException();
    }
    if (strlen($file->getName()) > Response::HTTP_OK) {
      throw new NameTooLongException();
    }
  }
}
